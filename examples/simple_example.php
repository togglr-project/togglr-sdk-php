<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Togglr\Sdk\Exception\FeatureNotFoundException;
use Togglr\Sdk\Exception\TogglrException;
use Togglr\Sdk\Models\TrackEvent;
use Togglr\Sdk\RequestContext;
use Togglr\Sdk\TogglrSdk;

/**
 * Simple example of using togglr-sdk-php.
 */
function main(): void
{
    // Create client with default configuration
    $client = TogglrSdk::newClient(
        '42b6f8f1-630c-400c-97bd-a3454a07f700',
        [
            'base_url' => 'http://localhost:8090',
            'timeout' => 1.0,
            'cache' => [
                'enabled' => true,
                'max_size' => 1000,
                'ttl_seconds' => 10,
            ],
        ]
    );

    try {
        // Create request context
        $context = RequestContext::new()
            ->withUserId('user123')
            ->withCountry('US')
            ->withDeviceType('mobile')
            ->withOs('iOS')
            ->withOsVersion('15.0')
            ->withBrowser('Safari')
            ->withLanguage('en-US');

        // Evaluate feature flag
        try {
            $result = $client->evaluate('new_ui', $context);
            if ($result['found']) {
                echo "Feature enabled: {$result['enabled']}, value: {$result['value']}\n";
            } else {
                echo "Feature not found\n";
            }
        } catch (TogglrException $e) {
            echo "Error evaluating feature: {$e->getMessage()}\n";
        }

        // Simple enabled check
        try {
            $isEnabled = $client->isEnabled('new_ui', $context);
            echo 'Feature is enabled: ' . ($isEnabled ? 'true' : 'false') . "\n";
        } catch (FeatureNotFoundException $e) {
            echo "Feature not found\n";
        } catch (TogglrException $e) {
            echo "Error checking feature: {$e->getMessage()}\n";
        }

        // With default value
        $isEnabled = $client->isEnabledOrDefault('new_ui', $context, false);
        echo 'Feature enabled (with default): ' . ($isEnabled ? 'true' : 'false') . "\n";

        // Health check
        if ($client->healthCheck()) {
            echo "API is healthy\n";
        } else {
            echo "API is not healthy\n";
        }

        // Report an error for a feature
        try {
            $client->reportError(
                'new_ui',
                'timeout',
                'Service did not respond in 5s',
                ['service' => 'payment-gateway', 'timeout_ms' => 5000]
            );
            echo "Error reported successfully - queued for processing\n";
        } catch (TogglrException $e) {
            echo "Failed to report error: {$e->getMessage()}\n";
        }

        // Get feature health
        try {
            $health = $client->getFeatureHealth('new_ui');
            echo "Feature health: enabled=" . ($health->isEnabled() ? 'true' : 'false') . 
                 ", auto_disabled=" . ($health->isAutoDisabled() ? 'true' : 'false') . "\n";
            echo "Error rate: {$health->getErrorRate()}, threshold: {$health->getThreshold()}\n";
        } catch (TogglrException $e) {
            echo "Failed to get feature health: {$e->getMessage()}\n";
        }

        // Simple health check
        try {
            $isHealthy = $client->isFeatureHealthy('new_ui');
            echo "Feature new_ui is healthy: " . ($isHealthy ? 'true' : 'false') . "\n";
        } catch (TogglrException $e) {
            echo "Failed to check feature health: {$e->getMessage()}\n";
        }

        // Example: Track events for analytics
        // Track impression event (recommended for each evaluation)
        $impressionEvent = TrackEvent::new('A', TrackEvent::EVENT_TYPE_SUCCESS)
            ->withContext('user.id', 'user123')
            ->withContext('country', 'US')
            ->withContext('device_type', 'mobile')
            ->withDedupKey('impression-user123-new_ui');

        try {
            $client->trackEvent('new_ui', $impressionEvent);
            echo "Impression event tracked successfully\n";
        } catch (TogglrException $e) {
            echo "Error tracking impression event: {$e->getMessage()}\n";
        }

        // Track conversion event with reward
        $conversionEvent = TrackEvent::new('A', TrackEvent::EVENT_TYPE_SUCCESS)
            ->withReward(1.0)
            ->withContext('user.id', 'user123')
            ->withContext('conversion_type', 'purchase')
            ->withContext('order_value', 99.99)
            ->withDedupKey('conversion-user123-new_ui');

        try {
            $client->trackEvent('new_ui', $conversionEvent);
            echo "Conversion event tracked successfully\n";
        } catch (TogglrException $e) {
            echo "Error tracking conversion event: {$e->getMessage()}\n";
        }

        // Track error event
        $errorEvent = TrackEvent::new('B', TrackEvent::EVENT_TYPE_ERROR)
            ->withContext('user.id', 'user123')
            ->withContext('error_type', 'timeout')
            ->withContext('error_message', 'Service did not respond in 5s')
            ->withDedupKey('error-user123-new_ui');

        try {
            $client->trackEvent('new_ui', $errorEvent);
            echo "Error event tracked successfully\n";
        } catch (TogglrException $e) {
            echo "Error tracking error event: {$e->getMessage()}\n";
        }
    } finally {
        $client->close();
    }
}

main();
