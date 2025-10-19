<?php

declare(strict_types=1);

namespace Togglr\Sdk\Models;

use DateTime;

/**
 * TrackEvent represents an event to be tracked for analytics.
 */
class TrackEvent
{
    public const EVENT_TYPE_SUCCESS = 'success';
    public const EVENT_TYPE_FAILURE = 'failure';
    public const EVENT_TYPE_ERROR = 'error';

    private string $variantKey;
    private string $eventType;
    private float $reward;
    private array $context;
    private ?DateTime $createdAt;
    private ?string $dedupKey;

    public function __construct(
        string $variantKey,
        string $eventType,
        float $reward = 0.0,
        array $context = [],
        ?DateTime $createdAt = null,
        ?string $dedupKey = null
    ) {
        $this->variantKey = $variantKey;
        $this->eventType = $eventType;
        $this->reward = $reward;
        $this->context = $context;
        $this->createdAt = $createdAt;
        $this->dedupKey = $dedupKey;
    }

    /**
     * Create a new TrackEvent with success event type.
     */
    public static function new(string $variantKey, string $eventType): self
    {
        return new self($variantKey, $eventType);
    }

    /**
     * Add context to the event.
     */
    public function withContext(string $key, mixed $value): self
    {
        $new = clone $this;
        $new->context[$key] = $value;
        return $new;
    }

    /**
     * Add multiple context values to the event.
     */
    public function withContexts(array $contexts): self
    {
        $new = clone $this;
        $new->context = array_merge($new->context, $contexts);
        return $new;
    }

    /**
     * Set the reward value.
     */
    public function withReward(float $reward): self
    {
        $new = clone $this;
        $new->reward = $reward;
        return $new;
    }

    /**
     * Set the deduplication key.
     */
    public function withDedupKey(string $dedupKey): self
    {
        $new = clone $this;
        $new->dedupKey = $dedupKey;
        return $new;
    }

    /**
     * Set the creation timestamp.
     */
    public function withCreatedAt(DateTime $createdAt): self
    {
        $new = clone $this;
        $new->createdAt = $createdAt;
        return $new;
    }

    /**
     * Convert to API request format.
     */
    public function toApiRequest(): array
    {
        $data = [
            'variant_key' => $this->variantKey,
            'event_type' => $this->eventType,
            'reward' => $this->reward,
        ];

        if (!empty($this->context)) {
            $data['context'] = $this->context;
        }

        if ($this->createdAt !== null) {
            $data['created_at'] = $this->createdAt->format('c');
        }

        if ($this->dedupKey !== null) {
            $data['dedup_key'] = $this->dedupKey;
        }

        return $data;
    }

    public function getVariantKey(): string
    {
        return $this->variantKey;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function getReward(): float
    {
        return $this->reward;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getDedupKey(): ?string
    {
        return $this->dedupKey;
    }
}
