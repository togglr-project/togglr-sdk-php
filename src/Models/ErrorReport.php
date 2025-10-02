<?php

declare(strict_types=1);

namespace Togglr\Sdk\Models;

/**
 * Model for error reporting.
 *
 * Represents an error that occurred during feature execution.
 */
class ErrorReport
{
    private string $errorType;
    private string $errorMessage;
    private array $context;

    public function __construct(string $errorType, string $errorMessage, array $context = [])
    {
        $this->errorType = $errorType;
        $this->errorMessage = $errorMessage;
        $this->context = $context;
    }

    public function getErrorType(): string
    {
        return $this->errorType;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function toArray(): array
    {
        return [
            'error_type' => $this->errorType,
            'error_message' => $this->errorMessage,
            'context' => $this->context,
        ];
    }

    public static function new(string $errorType, string $errorMessage, array $context = []): self
    {
        return new self($errorType, $errorMessage, $context);
    }
}
