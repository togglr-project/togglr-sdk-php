<?php

declare(strict_types=1);

namespace Togglr\Sdk\Models;

/**
 * Model for feature health information.
 *
 * Represents the health status of a feature including error rates and auto-disable status.
 */
class FeatureHealth
{
    private ?string $featureKey;
    private ?string $environmentKey;
    private bool $enabled;
    private bool $autoDisabled;
    private float $errorRate;
    private float $threshold;
    private ?string $lastErrorAt;

    public function __construct(array $data = [])
    {
        $this->featureKey = $data['feature_key'] ?? null;
        $this->environmentKey = $data['environment_key'] ?? null;
        $this->enabled = $data['enabled'] ?? false;
        $this->autoDisabled = $data['auto_disabled'] ?? false;
        $this->errorRate = $data['error_rate'] ?? 0.0;
        $this->threshold = $data['threshold'] ?? 0.0;
        $this->lastErrorAt = $data['last_error_at'] ?? null;
    }

    public function getFeatureKey(): ?string
    {
        return $this->featureKey;
    }

    public function getEnvironmentKey(): ?string
    {
        return $this->environmentKey;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isAutoDisabled(): bool
    {
        return $this->autoDisabled;
    }

    public function getErrorRate(): float
    {
        return $this->errorRate;
    }

    public function getThreshold(): float
    {
        return $this->threshold;
    }

    public function getLastErrorAt(): ?string
    {
        return $this->lastErrorAt;
    }

    public function isHealthy(): bool
    {
        return $this->enabled && !$this->autoDisabled;
    }

    public function toArray(): array
    {
        return [
            'feature_key' => $this->featureKey,
            'environment_key' => $this->environmentKey,
            'enabled' => $this->enabled,
            'auto_disabled' => $this->autoDisabled,
            'error_rate' => $this->errorRate,
            'threshold' => $this->threshold,
            'last_error_at' => $this->lastErrorAt,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
