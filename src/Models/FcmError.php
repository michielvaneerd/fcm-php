<?php

namespace Mve\FcmPhp\Models;

use Symfony\Contracts\HttpClient\ResponseInterface;

class FcmError
{

    public const ERROR_UNKNOWN = 'UNKNOWN';
    public const ERROR_UNAUTHENTICATED = 'UNAUTHENTICATED';

    function __construct(public readonly int $code, public readonly string $error, public readonly string $message, public readonly string $content)
    {
    }

    public static function fromApiResponse(ResponseInterface $response): FcmError
    {
        $content = null;
        $error = null;
        $message = null;
        try {
            $content = $response->getContent(false);
            $contentAsArray = $response->toArray(false);
            $error = $contentAsArray['status'] ?? 'FCM_MISSING_ERROR';
            $message = $contentAsArray['message'] ?? 'FCM_MISSING_MESSAGE';
        } catch (\Exception $ex) {
            // Do nothing.
        }
        return new FcmError($response->getStatusCode(), $error, $message, $content);
    }
}
