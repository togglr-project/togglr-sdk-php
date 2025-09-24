<?php

declare(strict_types=1);

namespace Togglr\Sdk\Cache;

/**
 * A cache entry containing evaluation result.
 */
class CacheEntry
{
    private string $value;
    private bool $enabled;
    private bool $found;
    private int $timestamp;

    public function __construct(string $value, bool $enabled, bool $found)
    {
        $this->value = $value;
        $this->enabled = $enabled;
        $this->found = $found;
        $this->timestamp = time();
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isFound(): bool
    {
        return $this->found;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * Check if the entry is expired.
     */
    public function isExpired(int $ttl): bool
    {
        return time() - $this->timestamp > $ttl;
    }
}
