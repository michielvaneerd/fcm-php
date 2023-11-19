<?php

declare(strict_types=1);

namespace Mve\FcmPhp\Models;

/**
 * An error with specific Google Firebase information.
 * 
 * @link https://firebase.google.com/docs/reference/fcm/rest/v1/ErrorCode
 * @link https://cloud.google.com/resource-manager/docs/core_errors
 */
class FcmError
{
    /**
     * HTTP status 404
     * Unregistered token. For example when user removes the app.
     */
    public const ERROR_UNREGISTERED = 'UNREGISTERED';

    /**
     * HTTP status 404
     * Unregistered token. For example when user removes the app.
     */
    public const ERROR_NOT_FOUND = 'NOT_FOUND';
    
    /**
     * HTTP status 400
     * For example wrong format message or token.
     */
    public const ERROR_INVALID_ARGUMENT = 'INVALID_ARGUMENT';

    /**
     * HTTP status 429
     * For example when the sending rate of messages is too high.
     */
    public const ERROR_QUOTA_EXCEEDED = 'QUOTA_EXCEEDED';

    /**
     * HTTP status 503
     * The server is overloaded.
     */
    public const ERROR_UNAVAILABLE = 'UNAVAILABLE';

    /**
     * HTTP status 401
     * Invalid access token, for example when it is expired.
     */
    public const ERROR_UNAUTHENTICATED = 'UNAUTHENTICATED';

    /**
     * HTTP status 401
     * APNs certificate or web push auth key was invalid or missing.
     */
    public const ERROR_THIRD_PARTY_AUTH_ERROR = 'THIRD_PARTY_AUTH_ERROR';

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
