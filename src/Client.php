<?php

declare(strict_types=1);

namespace Togglr\Sdk;

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

// Generated API client
use Togglr\Client\Api\DefaultApi;
use Togglr\Client\Configuration as ApiConfiguration;
use Togglr\Client\Model\FeatureErrorReport;
use Togglr\Client\Model\FeatureHealth as ApiFeatureHealth;

/**
 * Togglr SDK client for feature flag evaluation.
 */
class Client
{
    private DefaultApi $apiClient;
    private ClientConfig $config;
    private ?CacheItemPoolInterface $cache;
    private ?LoggerInterface $logger;

    public function __construct(ClientConfig $config)
    {
        $this->config = $config;
        $this->logger = $config->getLogger();

        // Create API client
        $apiConfig = new ApiConfiguration();
        $apiConfig->setHost($config->getBaseUrl());
        $apiConfig->setApiKey('Authorization', $config->getApiKey());
        $this->apiClient = new DefaultApi(null, $apiConfig);

        // Initialize cache if enabled
        if ($config->isCacheEnabled()) {
            $this->cache = new ArrayAdapter($config->getCacheTtl(), true);
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
            $response = $this->apiClient->healthCheck();
            return isset($response['status']) && $response['status'] === 'ok';
        } catch (\Exception $e) {
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
            $response = $this->apiClient->evaluateFeature($featureKey, $context->toArray());

            if (isset($response['feature_key'], $response['enabled'], $response['value'])) {
                return [
                    'value' => $response['value'],
                    'enabled' => $response['enabled'],
                    'found' => true,
                ];
            }

            return ['value' => '', 'enabled' => false, 'found' => false];
        } catch (\Togglr\Client\ApiException $e) {
            $this->handleApiException($e, $featureKey);
        }
    }


    /**
     * Generate cache key for feature and context.
     */
    private function getCacheKey(string $featureKey, RequestContext $context): string
    {
        $contextString = json_encode($context->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $contextHash = md5($contextString);

        return "{$featureKey}_{$contextHash}";
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
     * @throws TogglrException
     */
    public function reportError(string $featureKey, string $errorType, string $errorMessage, array $context = []): void
    {
        $this->reportErrorWithRetries($featureKey, $errorType, $errorMessage, $context);
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
     * @throws TogglrException
     */
    private function reportErrorWithRetries(string $featureKey, string $errorType, string $errorMessage, array $context): void
    {
        $errorReport = Models\ErrorReport::new($errorType, $errorMessage, $context);
        
        $attempt = 0;
        $maxAttempts = $this->config->getRetries() + 1;

        while ($attempt < $maxAttempts) {
            try {
                $this->reportErrorSingle($featureKey, $errorReport);
                return; // Success
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
     * @throws TogglrException
     */
    private function reportErrorSingle(string $featureKey, Models\ErrorReport $errorReport): void
    {
        try {
            // Convert our ErrorReport to generated FeatureErrorReport
            $apiErrorReport = new FeatureErrorReport([
                'error_type' => $errorReport->getErrorType(),
                'error_message' => $errorReport->getErrorMessage(),
                'context' => $errorReport->getContext(),
            ]);

            $this->apiClient->reportFeatureError($featureKey, $apiErrorReport);
            // Success - error queued for processing
        } catch (\Togglr\Client\ApiException $e) {
            $this->handleApiException($e, $featureKey);
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
        try {
            $apiHealth = $this->apiClient->getFeatureHealth($featureKey);
            return $this->convertFeatureHealth($apiHealth);
        } catch (\Togglr\Client\ApiException $e) {
            $this->handleApiException($e, $featureKey);
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

    /**
     * Handle API exceptions and convert to appropriate SDK exceptions.
     *
     * @throws TogglrException
     */
    private function handleApiException(\Togglr\Client\ApiException $e, string $featureKey): void
    {
        $statusCode = $e->getCode();
        
        switch ($statusCode) {
            case 401:
                throw new UnauthorizedException('Authentication required');
            case 400:
                throw new BadRequestException('Bad request');
            case 404:
                throw new FeatureNotFoundException("Feature {$featureKey} not found");
            case 500:
                throw new InternalServerException('Internal server error');
            default:
                throw new TogglrException("API error: {$e->getMessage()}", $statusCode);
        }
    }

    /**
     * Convert API FeatureHealth to SDK FeatureHealth.
     */
    private function convertFeatureHealth(ApiFeatureHealth $apiHealth): Models\FeatureHealth
    {
        return Models\FeatureHealth::new(
            featureKey: $apiHealth->getFeatureKey(),
            environmentKey: $apiHealth->getEnvironmentKey(),
            enabled: $apiHealth->getEnabled() ?? false,
            autoDisabled: $apiHealth->getAutoDisabled() ?? false,
            errorRate: $apiHealth->getErrorRate() ?? 0.0,
            threshold: $apiHealth->getThreshold() ?? 0.0,
            lastErrorAt: $apiHealth->getLastErrorAt()
        );
    }
}
