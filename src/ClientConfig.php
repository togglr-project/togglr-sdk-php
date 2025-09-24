<?php

declare(strict_types=1);

namespace Togglr\Sdk;

use Psr\Log\LoggerInterface;

/**
 * Configuration for the Togglr client.
 */
class ClientConfig
{
    private string $apiKey;
    private string $baseUrl;
    private float $timeout;
    private int $retries;
    private BackoffConfig $backoff;
    private bool $cacheEnabled;
    private int $cacheMaxSize;
    private int $cacheTtl;
    private ?LoggerInterface $logger;

    public function __construct(
        string $apiKey,
        string $baseUrl = 'http://localhost:8090',
        float $timeout = 0.8,
        int $retries = 2,
        ?BackoffConfig $backoff = null,
        bool $cacheEnabled = false,
        int $cacheMaxSize = 100,
        int $cacheTtl = 5,
        ?LoggerInterface $logger = null
    ) {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
        $this->retries = $retries;
        $this->backoff = $backoff ?? new BackoffConfig();
        $this->cacheEnabled = $cacheEnabled;
        $this->cacheMaxSize = $cacheMaxSize;
        $this->cacheTtl = $cacheTtl;
        $this->logger = $logger;
    }

    /**
     * Create a default configuration with the provided API key.
     */
    public static function default(string $apiKey): self
    {
        return new self($apiKey);
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getTimeout(): float
    {
        return $this->timeout;
    }

    public function getRetries(): int
    {
        return $this->retries;
    }

    public function getBackoff(): BackoffConfig
    {
        return $this->backoff;
    }

    public function isCacheEnabled(): bool
    {
        return $this->cacheEnabled;
    }

    public function getCacheMaxSize(): int
    {
        return $this->cacheMaxSize;
    }

    public function getCacheTtl(): int
    {
        return $this->cacheTtl;
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Set the base URL.
     */
    public function withBaseUrl(string $baseUrl): self
    {
        $new = clone $this;
        $new->baseUrl = rtrim($baseUrl, '/');

        return $new;
    }

    /**
     * Set the timeout in seconds.
     */
    public function withTimeout(float $timeout): self
    {
        $new = clone $this;
        $new->timeout = $timeout;

        return $new;
    }

    /**
     * Set the number of retries.
     */
    public function withRetries(int $retries): self
    {
        $new = clone $this;
        $new->retries = $retries;

        return $new;
    }

    /**
     * Configure retry backoff.
     */
    public function withBackoff(BackoffConfig $backoff): self
    {
        $new = clone $this;
        $new->backoff = $backoff;

        return $new;
    }

    /**
     * Configure caching.
     */
    public function withCache(bool $enabled = true, int $maxSize = 100, int $ttl = 5): self
    {
        $new = clone $this;
        $new->cacheEnabled = $enabled;
        $new->cacheMaxSize = $maxSize;
        $new->cacheTtl = $ttl;

        return $new;
    }

    /**
     * Set a custom logger.
     */
    public function withLogger(LoggerInterface $logger): self
    {
        $new = clone $this;
        $new->logger = $logger;

        return $new;
    }
}
