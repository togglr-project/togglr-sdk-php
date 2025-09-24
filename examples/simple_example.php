<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Togglr\Sdk\Exception\FeatureNotFoundException;
use Togglr\Sdk\Exception\TogglrException;
use Togglr\Sdk\RequestContext;
use Togglr\Sdk\TogglrSdk;

/**
 * Simple example of using togglr-sdk-php.
 */
function main(): void
{
    // Create client with default configuration
    $client = TogglrSdk::newClient(
        'your-api-key-here',
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
    } finally {
        $client->close();
    }
}

main();
