<?php

declare(strict_types=1);

namespace Togglr\Sdk\Exception;

/**
 * Raised when authentication fails.
 */
class UnauthorizedException extends TogglrException
{
    public function __construct(string $message = 'Authentication required', ?\Throwable $previous = null)
    {
        parent::__construct($message, 401, $previous);
    }
}
