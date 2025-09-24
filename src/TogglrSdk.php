<?php

declare(strict_types=1);

namespace Togglr\Sdk;

use Psr\Log\LoggerInterface;

/**
 * Main entry point for the Togglr SDK.
 */
class TogglrSdk
{
    /**
     * Create a new client with the given API key and options.
     */
    public static function newClient(string $apiKey, array $options = []): Client
    {
        $config = ClientConfig::default($apiKey);

        // Apply options
        if (isset($options['base_url'])) {
            $config = $config->withBaseUrl($options['base_url']);
        }

        if (isset($options['timeout'])) {
            $config = $config->withTimeout($options['timeout']);
        }

        if (isset($options['retries'])) {
            $config = $config->withRetries($options['retries']);
        }

        if (isset($options['cache'])) {
            $cacheConfig = $options['cache'];
            $enabled = $cacheConfig['enabled'] ?? true;
            $maxSize = $cacheConfig['max_size'] ?? 100;
            $ttl = $cacheConfig['ttl_seconds'] ?? 5;
            $config = $config->withCache($enabled, $maxSize, $ttl);
        }

        if (isset($options['backoff'])) {
            $backoffConfig = $options['backoff'];
            $baseDelay = $backoffConfig['base_delay'] ?? 0.1;
            $maxDelay = $backoffConfig['max_delay'] ?? 2.0;
            $factor = $backoffConfig['factor'] ?? 2.0;
            $config = $config->withBackoff(new BackoffConfig($baseDelay, $maxDelay, $factor));
        }

        if (isset($options['logger']) && $options['logger'] instanceof LoggerInterface) {
            $config = $config->withLogger($options['logger']);
        }

        return new Client($config);
    }
}
