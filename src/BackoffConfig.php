<?php

declare(strict_types=1);

namespace Togglr\Sdk;

/**
 * Configuration for retry backoff.
 */
class BackoffConfig
{
    private float $baseDelay;
    private float $maxDelay;
    private float $factor;

    public function __construct(
        float $baseDelay = 0.1,
        float $maxDelay = 2.0,
        float $factor = 2.0
    ) {
        $this->baseDelay = $baseDelay;
        $this->maxDelay = $maxDelay;
        $this->factor = $factor;
    }

    public function getBaseDelay(): float
    {
        return $this->baseDelay;
    }

    public function getMaxDelay(): float
    {
        return $this->maxDelay;
    }

    public function getFactor(): float
    {
        return $this->factor;
    }

    /**
     * Calculate delay for the given attempt.
     */
    public function calculateDelay(int $attempt): float
    {
        $delay = $this->baseDelay;

        for ($i = 1; $i < $attempt; $i++) {
            $delay *= $this->factor;
            if ($delay > $this->maxDelay) {
                $delay = $this->maxDelay;
                break;
            }
        }

        return $delay;
    }
}
