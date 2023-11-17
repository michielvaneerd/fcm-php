<?php

namespace Mve\FcmPhp\Models;

/**
 * Result object that contains information about each Message, like whether it has been sent successfully, has an invalid token or has some error.
 * // TODO: update this so we can use it for subscribeToTopics as well. Because here we get also the tokens + status in an array
 */
class SendAllResult
{
    /**
     * @param int[] $sentIds Message id's that have been sent successfully.
     * @param int[] $invalidIds Message id's that have invalid tokens.
     * @param array<int, FcmError> $errorIds Message id's and errors.
     */
    function __construct(public array $sentIds = [], public array $invalidIds = [], public array $errorIds = [])
    {
    }
}
