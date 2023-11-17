<?php

namespace Mve\FcmPhp\Models;

use Symfony\Contracts\HttpClient\ResponseInterface;

class FcmError
{

    public const ERROR_UNKNOWN = 'UNKNOWN';
    public const ERROR_UNAUTHENTICATED = 'UNAUTHENTICATED';
    public const ERROR_NOT_FOUND = 'NOT_FOUND';
    public const ERROR_UNREGISTERED = 'UNREGISTERED';

    function __construct(public readonly int $code, public readonly string $error, public readonly string $message, public readonly string $content)
    {
    }

}
