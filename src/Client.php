<?php

declare(strict_types=1);

namespace Togglr\Sdk;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Togglr\Sdk\Cache\CacheEntry;
use Togglr\Sdk\Exception\BadRequestException;
use Togglr\Sdk\Exception\FeatureNotFoundException;
use Togglr\Sdk\Exception\InternalServerException;
use Togglr\Sdk\Exception\NotFoundException;
use Togglr\Sdk\Exception\TogglrException;
use Togglr\Sdk\Exception\TooManyRequestsException;
use Togglr\Sdk\Exception\UnauthorizedException;

/**
 * Togglr SDK client for feature flag evaluation.
 */
class Client
{
    private GuzzleClient $httpClient;
    private ClientConfig $config;
    private ?CacheItemPoolInterface $cache;
    private ?LoggerInterface $logger;

    public function __construct(ClientConfig $config)
    {
        $this->config = $config;
        $this->logger = $config->getLogger();

        // Create HTTP client
        $this->httpClient = new GuzzleClient([
            'base_uri' => $config->getBaseUrl(),
            'timeout' => $config->getTimeout(),
            'headers' => [
                'Authorization' => $config->getApiKey(),
                'Content-Type' => 'application/json',
                'User-Agent' => 'togglr-sdk-php/1.0.0',
            ],
        ]);

        // Initialize cache if enabled
        if ($config->isCacheEnabled()) {
            $this->cache = new ArrayAdapter($config->getCacheTtl(), $config->getCacheMaxSize());
        } else {
            $this->cache = null;
        }
    }

    /**
     * Close the client and clean up resources.
     */
    public function close(): void
    {
        // Guzzle client doesn't need explicit cleanup
        if ($this->cache) {
            $this->cache->clear();
        }
    }

    /**
     * Perform a health check on the API.
     */
    public function healthCheck(): bool
    {
        try {
            $response = $this->httpClient->get('/sdk/v1/health');
            $data = json_decode($response->getBody()->getContents(), true);

            return isset($data['status']) && $data['status'] === 'ok';
        } catch (GuzzleException $e) {
            $this->log('Health check failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Evaluate a feature flag.
     *
     * @return array{value: string, enabled: bool, found: bool}
     * @throws TogglrException
     */
    public function evaluate(string $featureKey, RequestContext $context): array
    {
        return $this->evaluateWithRetries($featureKey, $context);
    }

    /**
     * Check if a feature is enabled.
     *
     * @throws TogglrException
     * @throws FeatureNotFoundException
     */
    public function isEnabled(string $featureKey, RequestContext $context): bool
    {
        $result = $this->evaluate($featureKey, $context);

        if (!$result['found']) {
            throw new FeatureNotFoundException("Feature '{$featureKey}' not found");
        }

        return $result['enabled'];
    }

    /**
     * Check if a feature is enabled, returning default on error.
     */
    public function isEnabledOrDefault(string $featureKey, RequestContext $context, bool $default = false): bool
    {
        try {
            return $this->isEnabled($featureKey, $context);
        } catch (TogglrException $e) {
            $this->log('Evaluation failed, using default', [
                'feature_key' => $featureKey,
                'error' => $e->getMessage(),
                'default' => $default,
            ]);

            return $default;
        }
    }

    /**
     * Evaluate feature with retry logic.
     *
     * @return array{value: string, enabled: bool, found: bool}
     * @throws TogglrException
     */
    private function evaluateWithRetries(string $featureKey, RequestContext $context): array
    {
        // Check cache first
        if ($this->cache) {
            $cacheKey = $this->getCacheKey($featureKey, $context);
            $cachedItem = $this->cache->getItem($cacheKey);

            if ($cachedItem->isHit()) {
                $entry = $cachedItem->get();
                if ($entry instanceof CacheEntry) {
                    $this->log('Cache hit', ['feature_key' => $featureKey, 'cache_key' => $cacheKey]);

                    return [
                        'value' => $entry->getValue(),
                        'enabled' => $entry->isEnabled(),
                        'found' => $entry->isFound(),
                    ];
                }
            }
        }

        $lastException = null;

        for ($attempt = 0; $attempt <= $this->config->getRetries(); $attempt++) {
            if ($attempt > 0) {
                $delay = $this->config->getBackoff()->calculateDelay($attempt);
                $this->log('Retrying after delay', ['attempt' => $attempt, 'delay' => $delay]);
                usleep((int) ($delay * 1000000)); // Convert to microseconds
            }

            try {
                $result = $this->evaluateSingle($featureKey, $context);

                // Cache result if successful
                if ($this->cache) {
                    $cacheKey = $this->getCacheKey($featureKey, $context);
                    $cachedItem = $this->cache->getItem($cacheKey);
                    $cachedItem->set(new CacheEntry($result['value'], $result['enabled'], $result['found']));
                    $this->cache->save($cachedItem);
                }

                return $result;
            } catch (TogglrException $e) {
                $lastException = $e;

                // Don't retry on client errors (4xx)
                if ($e instanceof UnauthorizedException ||
                    $e instanceof BadRequestException ||
                    $e instanceof NotFoundException) {
                    break;
                }

                $this->log('Retrying due to error', ['attempt' => $attempt, 'error' => $e->getMessage()]);
            }
        }

        throw $lastException ?? new TogglrException('Evaluation failed');
    }

    /**
     * Perform a single evaluation request.
     *
     * @return array{value: string, enabled: bool, found: bool}
     * @throws TogglrException
     */
    private function evaluateSingle(string $featureKey, RequestContext $context): array
    {
        try {
            $response = $this->httpClient->post("/sdk/v1/features/{$featureKey}/evaluate", [
                'json' => $context->toArray(),
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['feature_key'], $data['enabled'], $data['value'])) {
                return [
                    'value' => $data['value'],
                    'enabled' => $data['enabled'],
                    'found' => true,
                ];
            }

            return ['value' => '', 'enabled' => false, 'found' => false];
        } catch (RequestException $e) {
            $this->handleHttpException($e);
        }
    }

    /**
     * Handle HTTP exceptions and convert to appropriate Togglr exceptions.
     *
     * @throws TogglrException
     */
    private function handleHttpException(RequestException $e): void
    {
        $statusCode = $e->getResponse()?->getStatusCode() ?? 0;

        switch ($statusCode) {
            case 401:
                throw new UnauthorizedException('Authentication required');
            case 400:
                throw new BadRequestException('Bad request');
            case 404:
                throw new NotFoundException('Resource not found');
            case 429:
                throw new TooManyRequestsException('Too many requests');
            case 500:
            default:
                if ($statusCode >= 500) {
                    throw new InternalServerException('Internal server error');
                }
                throw new TogglrException('API error: ' . $statusCode);
        }
    }

    /**
     * Generate cache key for feature and context.
     */
    private function getCacheKey(string $featureKey, RequestContext $context): string
    {
        $contextString = json_encode($context->toArray(), JSON_SORT_KEYS);
        $contextHash = md5($contextString);

        return "{$featureKey}:{$contextHash}";
    }

    /**
     * Log a message if logger is available.
     */
    private function log(string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->info($message, $context);
        }
    }

    /**
     * Report an error for a feature.
     *
     * @return array{0: FeatureHealth, 1: bool} Returns [health, is_pending]
     * @throws TogglrException
     */
    public function reportError(string $featureKey, string $errorType, string $errorMessage, array $context = []): array
    {
        return $this->reportErrorWithRetries($featureKey, $errorType, $errorMessage, $context);
    }

    /**
     * Get feature health information.
     *
     * @throws TogglrException
     */
    public function getFeatureHealth(string $featureKey): Models\FeatureHealth
    {
        return $this->getFeatureHealthWithRetries($featureKey);
    }

    /**
     * Check if a feature is healthy.
     *
     * @throws TogglrException
     */
    public function isFeatureHealthy(string $featureKey): bool
    {
        $health = $this->getFeatureHealth($featureKey);
        return $health->isHealthy();
    }

    /**
     * Report error with retry logic.
     *
     * @return array{0: FeatureHealth, 1: bool} Returns [health, is_pending]
     * @throws TogglrException
     */
    private function reportErrorWithRetries(string $featureKey, string $errorType, string $errorMessage, array $context): array
    {
        $errorReport = Models\ErrorReport::new($errorType, $errorMessage, $context);
        
        $attempt = 0;
        $maxAttempts = $this->config->getRetries() + 1;

        while ($attempt < $maxAttempts) {
            try {
                return $this->reportErrorSingle($featureKey, $errorReport);
            } catch (TogglrException $e) {
                $attempt++;
                
                if ($attempt >= $maxAttempts || !$this->shouldRetry($e)) {
                    throw $e;
                }

                $delay = $this->config->getBackoff()->calculateDelay($attempt);
                usleep((int) ($delay * 1000000)); // Convert to microseconds
            }
        }

        throw new TogglrException('Max retry attempts exceeded');
    }

    /**
     * Report error single attempt.
     *
     * @return array{0: FeatureHealth, 1: bool} Returns [health, is_pending]
     * @throws TogglrException
     */
    private function reportErrorSingle(string $featureKey, Models\ErrorReport $errorReport): array
    {
        $url = "/sdk/v1/features/{$featureKey}/report-error";
        $body = json_encode($errorReport->toArray());

        try {
            $response = $this->httpClient->post($url, [
                'body' => $body,
            ]);

            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody()->getContents(), true);

            if ($statusCode === 200) {
                $health = Models\FeatureHealth::fromArray($responseData);
                return [$health, false]; // health, is_pending
            } elseif ($statusCode === 202) {
                $health = Models\FeatureHealth::fromArray($responseData);
                return [$health, true]; // health, is_pending
            } else {
                throw new TogglrException("Unexpected status code: {$statusCode}");
            }
        } catch (RequestException $e) {
            $this->handleHttpError($e, $featureKey);
        }
    }

    /**
     * Get feature health with retry logic.
     *
     * @throws TogglrException
     */
    private function getFeatureHealthWithRetries(string $featureKey): Models\FeatureHealth
    {
        $attempt = 0;
        $maxAttempts = $this->config->getRetries() + 1;

        while ($attempt < $maxAttempts) {
            try {
                return $this->getFeatureHealthSingle($featureKey);
            } catch (TogglrException $e) {
                $attempt++;
                
                if ($attempt >= $maxAttempts || !$this->shouldRetry($e)) {
                    throw $e;
                }

                $delay = $this->config->getBackoff()->calculateDelay($attempt);
                usleep((int) ($delay * 1000000)); // Convert to microseconds
            }
        }

        throw new TogglrException('Max retry attempts exceeded');
    }

    /**
     * Get feature health single attempt.
     *
     * @throws TogglrException
     */
    private function getFeatureHealthSingle(string $featureKey): Models\FeatureHealth
    {
        $url = "/sdk/v1/features/{$featureKey}/health";

        try {
            $response = $this->httpClient->get($url);
            $responseData = json_decode($response->getBody()->getContents(), true);

            return Models\FeatureHealth::fromArray($responseData);
        } catch (RequestException $e) {
            $this->handleHttpError($e, $featureKey);
        }
    }

    /**
     * Check if an exception should trigger a retry.
     */
    private function shouldRetry(TogglrException $e): bool
    {
        return !($e instanceof UnauthorizedException) &&
               !($e instanceof BadRequestException) &&
               !($e instanceof FeatureNotFoundException);
    }

    /**
     * Handle HTTP errors and convert to appropriate exceptions.
     *
     * @throws TogglrException
     */
    private function handleHttpError(RequestException $e, string $featureKey): void
    {
        $response = $e->getResponse();
        
        if (!$response) {
            throw new TogglrException('Request failed: ' . $e->getMessage());
        }

        $statusCode = $response->getStatusCode();

        switch ($statusCode) {
            case 401:
                throw new UnauthorizedException('Authentication required');
            case 400:
                throw new BadRequestException('Bad request');
            case 404:
                throw new FeatureNotFoundException("Feature '{$featureKey}' not found");
            case 429:
                throw new TooManyRequestsException('Too many requests');
            case 500:
                throw new InternalServerException('Internal server error');
            default:
                throw new TogglrException("HTTP {$statusCode}");
        }
    }
}
