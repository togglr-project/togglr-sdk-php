<?php

declare(strict_types=1);

namespace Togglr\Sdk\Exception;

/**
 * Raised when a feature flag is not found.
 */
class FeatureNotFoundException extends TogglrException
{
    public function __construct(string $message = 'Feature not found', \Throwable $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }
}
