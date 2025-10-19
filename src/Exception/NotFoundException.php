<?php

declare(strict_types=1);

namespace Togglr\Sdk\Exception;

/**
 * Raised when a resource is not found.
 */
class NotFoundException extends TogglrException
{
    public function __construct(string $message = 'Resource not found', ?\Throwable $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }
}
