<?php

declare(strict_types=1);

namespace Mve\FcmPhp\Models;

/**
 * An error with specific Google Firebase information.
 */
class FcmError
{

    public const ERROR_UNKNOWN = 'UNKNOWN';
    public const ERROR_UNAUTHENTICATED = 'UNAUTHENTICATED';
    public const ERROR_NOT_FOUND = 'NOT_FOUND';
    public const ERROR_UNREGISTERED = 'UNREGISTERED';

    /**
     * @param int $code The error code (will be the same as the HTTP status code).
     * @param string $error The error name.
     * @param string $message A description of the error.
     * @param string $content The raw content of the Google API response.
     */
    function __construct(public readonly int $code, public readonly string $error, public readonly string $message, public readonly string $content)
    {
    }

}
