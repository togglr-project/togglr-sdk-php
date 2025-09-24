<?php

declare(strict_types=1);

namespace Togglr\Sdk\Exception;

/**
 * Raised when rate limit is exceeded.
 */
class TooManyRequestsException extends TogglrException
{
    public function __construct(string $message = 'Too many requests', \Throwable $previous = null)
    {
        parent::__construct($message, 429, $previous);
    }
}
