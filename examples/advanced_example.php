<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Togglr\Sdk\Exception\FeatureNotFoundException;
use Togglr\Sdk\Exception\TogglrException;
use Togglr\Sdk\Models\ErrorReport;
use Togglr\Sdk\Models\FeatureHealth;
use Togglr\Sdk\RequestContext;
use Togglr\Sdk\TogglrSdk;

/**
 * Advanced example of using togglr-sdk-php.
 */
function main(): void
{
    echo "=== Togglr SDK Advanced Example ===\n";

    // Create client with advanced configuration
    $client = TogglrSdk::newClient(
        'your-api-key-here',
        [
            'base_url' => 'http://localhost:8090',
            'timeout' => 2.0,
            'retries' => 5,
            'cache' => [
                'enabled' => true,
                'max_size' => 2000,
                'ttl_seconds' => 30,
            ],
            'backoff' => [
                'base_delay' => 0.2,
                'max_delay' => 5.0,
                'factor' => 1.5,
            ],
        ]
    );

    try {
        // Create request context
        $context = RequestContext::new()
            ->withUserId('user456')
            ->withCountry('CA')
            ->withUserEmail('user@example.ca')
            ->withDeviceType('desktop')
            ->withOs('macOS')
            ->withOsVersion('12.0')
            ->set('subscription', 'premium')
            ->set('region', 'north');

        echo "Context: " . json_encode($context->toArray()) . "\n";

        $featureKey = 'advanced_analytics';

        // Evaluate feature
        echo "\n=== Feature Evaluation ===\n";
        try {
            $result = $client->evaluate($featureKey, $context);
            echo "Feature evaluation result:\n";
            echo "  Found: " . ($result['found'] ? 'true' : 'false') . "\n";
            echo "  Enabled: " . ($result['enabled'] ? 'true' : 'false') . "\n";
            echo "  Value: {$result['value']}\n";
        } catch (TogglrException $e) {
            echo "Feature evaluation failed: {$e->getMessage()}\n";
        }

        // Test different error types
        echo "\n=== Error Reporting Examples ===\n";
        
        $errorExamples = [
            ['timeout', 'Service timeout after 10s', ['timeout_ms' => 10000, 'service' => 'analytics']],
            ['validation', 'Invalid user data provided', ['field' => 'email', 'value' => 'invalid-email']],
            ['service_unavailable', 'External service is down', ['service' => 'database', 'region' => 'us-east-1']],
            ['rate_limit', 'Too many requests', ['limit' => 100, 'current' => 150, 'window' => '1m']],
        ];

        foreach ($errorExamples as [$errorType, $message, $contextData]) {
            try {
                $client->reportError($featureKey, $errorType, $message, $contextData);
                echo "Reported {$errorType} error successfully - queued for processing\n";
            } catch (TogglrException $e) {
                echo "Failed to report {$errorType} error: {$e->getMessage()}\n";
            }
            echo "\n";
        }

        // Feature health monitoring
        echo "=== Feature Health Monitoring ===\n";
        
        try {
            $health = $client->getFeatureHealth($featureKey);
            echo "Feature: {$health->getFeatureKey()}\n";
            echo "Environment: {$health->getEnvironmentKey()}\n";
            echo "Enabled: " . ($health->isEnabled() ? 'true' : 'false') . "\n";
            echo "Auto Disabled: " . ($health->isAutoDisabled() ? 'true' : 'false') . "\n";
            echo "Error Rate: {$health->getErrorRate()}\n";
            echo "Threshold: {$health->getThreshold()}\n";
            echo "Last Error At: {$health->getLastErrorAt()}\n";
            echo "Is Healthy: " . ($health->isHealthy() ? 'true' : 'false') . "\n";
        } catch (TogglrException $e) {
            echo "Failed to get feature health: {$e->getMessage()}\n";
        }

        // Simple health check
        echo "\n=== Simple Health Check ===\n";
        try {
            $isHealthy = $client->isFeatureHealthy($featureKey);
            echo "Feature {$featureKey} is healthy: " . ($isHealthy ? 'true' : 'false') . "\n";
        } catch (TogglrException $e) {
            echo "Health check failed: {$e->getMessage()}\n";
        }

        // Multiple features health check
        echo "\n=== Multiple Features Health Check ===\n";
        $features = ['advanced_analytics', 'new_ui', 'beta_features', 'experimental_api'];
        
        foreach ($features as $feature) {
            try {
                $isHealthy = $client->isFeatureHealthy($feature);
                $status = $isHealthy ? 'healthy' : 'unhealthy';
                echo "Feature {$feature}: {$status}\n";
            } catch (TogglrException $e) {
                echo "Feature {$feature}: error - {$e->getMessage()}\n";
            }
        }

        // Health check
        echo "\n=== System Health Check ===\n";
        if ($client->healthCheck()) {
            echo "System health: healthy\n";
        } else {
            echo "System health: unhealthy\n";
        }

        // Demonstrate ErrorReport model
        echo "\n=== ErrorReport Model Example ===\n";
        $errorReport = ErrorReport::new(
            'timeout',
            'Service timeout',
            ['service' => 'api', 'timeout_ms' => 5000]
        );
        echo "Error Report: " . json_encode($errorReport->toArray()) . "\n";

        // Demonstrate FeatureHealth model
        echo "\n=== FeatureHealth Model Example ===\n";
        $healthData = [
            'feature_key' => 'test_feature',
            'enabled' => true,
            'auto_disabled' => false,
            'error_rate' => 0.05,
            'threshold' => 0.1,
        ];
        $health = FeatureHealth::fromArray($healthData);
        echo "Feature Health: " . json_encode($health->toArray()) . "\n";
        echo "Is Healthy: " . ($health->isHealthy() ? 'true' : 'false') . "\n";

    } finally {
        $client->close();
        echo "\nClient closed\n";
    }
}

main();