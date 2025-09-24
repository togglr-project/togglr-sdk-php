<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Togglr\Sdk\BackoffConfig;
use Togglr\Sdk\ClientConfig;
use Togglr\Sdk\Exception\TogglrException;
use Togglr\Sdk\RequestContext;

/**
 * Custom logger implementation.
 */
class CustomLogger implements LoggerInterface
{
    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = empty($context) ? '' : ' ' . json_encode($context);
        echo "[{$timestamp}] [{$level}] {$message}{$contextStr}\n";
    }
}

/**
 * Custom metrics implementation.
 */
class CustomMetrics
{
    private int $evaluateRequests = 0;
    private int $cacheHits = 0;
    private int $cacheMisses = 0;
    private int $errors = 0;

    public function incEvaluateRequest(): void
    {
        $this->evaluateRequests++;
    }

    public function incCacheHit(): void
    {
        $this->cacheHits++;
    }

    public function incCacheMiss(): void
    {
        $this->cacheMisses++;
    }

    public function incEvaluateError(string $errorCode): void
    {
        $this->errors++;
    }

    public function observeEvaluateLatency(float $latency): void
    {
        // Implement latency tracking if needed
    }

    public function printStats(): void
    {
        echo "Metrics: requests={$this->evaluateRequests}, " .
             "cache_hits={$this->cacheHits}, cache_misses={$this->cacheMisses}, " .
             "errors={$this->errors}\n";
    }
}

/**
 * Advanced example of using togglr-sdk-php with custom configuration.
 */
function main(): void
{
    // Create custom logger and metrics
    $logger = new CustomLogger();
    $metrics = new CustomMetrics();

    // Create custom configuration
    $config = ClientConfig::default('your-api-key-here')
        ->withBaseUrl('http://localhost:8090')
        ->withTimeout(2.0)
        ->withRetries(3)
        ->withCache(true, 500, 30)
        ->withBackoff(new BackoffConfig(0.2, 5.0, 1.5))
        ->withLogger($logger);

    // Create client with custom configuration
    $client = new \Togglr\Sdk\Client($config);

    try {
        // Test health check
        if (!$client->healthCheck()) {
            echo "API is not healthy, exiting\n";

            return;
        }

        // Create different contexts for testing
        $contexts = [
            RequestContext::new()
                ->withUserId('user1')
                ->withCountry('US')
                ->withDeviceType('desktop')
                ->withOs('Windows')
                ->withBrowser('Chrome'),

            RequestContext::new()
                ->withUserId('user2')
                ->withCountry('RU')
                ->withDeviceType('mobile')
                ->withOs('Android')
                ->withOsVersion('12.0')
                ->withLanguage('ru-RU'),

            RequestContext::new()
                ->withUserId('user3')
                ->withCountry('DE')
                ->withDeviceType('tablet')
                ->withOs('iOS')
                ->withOsVersion('16.0')
                ->withBrowser('Safari')
                ->withLanguage('de-DE')
                ->withAge(25)
                ->withGender('female'),
        ];

        // Test different feature flags
        $featureKeys = ['new_ui', 'beta_features', 'premium_content', 'dark_mode'];

        foreach ($contexts as $i => $context) {
            echo "\n--- Testing context " . ($i + 1) . " ---\n";
            echo 'Context: ' . json_encode($context->toArray()) . "\n";

            foreach ($featureKeys as $featureKey) {
                try {
                    // Full evaluation
                    $result = $client->evaluate($featureKey, $context);
                    if ($result['found']) {
                        echo "  {$featureKey}: enabled=" . ($result['enabled'] ? 'true' : 'false') .
                             ", value={$result['value']}\n";
                    } else {
                        echo "  {$featureKey}: not found\n";
                    }

                    // Simple enabled check with default
                    $isEnabled = $client->isEnabledOrDefault($featureKey, $context, false);
                    echo "  {$featureKey} (with default): " . ($isEnabled ? 'true' : 'false') . "\n";
                } catch (TogglrException $e) {
                    echo "  {$featureKey}: error - {$e->getMessage()}\n";
                }
            }
        }

        // Print metrics
        echo "\n--- Metrics ---\n";
        $metrics->printStats();

        // Test caching by evaluating the same feature multiple times
        echo "\n--- Testing cache ---\n";
        $context = $contexts[0];
        $featureKey = 'new_ui';

        for ($i = 0; $i < 5; $i++) {
            $startTime = microtime(true);
            try {
                $result = $client->evaluate($featureKey, $context);
                $elapsed = microtime(true) - $startTime;
                echo '  Attempt ' . ($i + 1) . ': ' . number_format($elapsed, 3) . 's, ' .
                     'enabled=' . ($result['enabled'] ? 'true' : 'false') .
                     ", value={$result['value']}\n";
            } catch (TogglrException $e) {
                echo '  Attempt ' . ($i + 1) . ": error - {$e->getMessage()}\n";
            }
        }

        // Print final metrics
        echo "\n--- Final Metrics ---\n";
        $metrics->printStats();
    } finally {
        $client->close();
    }
}

main();
