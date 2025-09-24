<?php

declare(strict_types=1);

namespace Togglr\Sdk\Exception;

/**
 * Raised when the server encounters an internal error.
 */
class InternalServerException extends TogglrException
{
    public function __construct(string $message = 'Internal server error', \Throwable $previous = null)
    {
        parent::__construct($message, 500, $previous);
    }
}
