<?php

declare(strict_types=1);

namespace Togglr\Sdk\Exception;

/**
 * Raised when the request is malformed.
 */
class BadRequestException extends TogglrException
{
    public function __construct(string $message = 'Bad request', \Throwable $previous = null)
    {
        parent::__construct($message, 400, $previous);
    }
}
