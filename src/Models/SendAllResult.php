<?php

namespace Mve\FcmPhp\Models;

/**
 * Result object that contains information about each Message, like whether it has been sent successfully, has an invalid token or has some error.
 * // TODO: update this so we can use it for subscribeToTopics as well. Because here we get also the tokens + status in an array
 */
class SendAllResult
{
    /**
     * @param array<int, string> $sent Message id's that have been sent successfully and their Firebase message ID.
     * @param array<int, FcmError> $unregistered Message id's that have unregistered tokens (these can be removed).
     * @param array<int, FcmError> $errors Message id's and FcmError errors.
     */
    function __construct(public array $sent = [], public array $unregistered = [], public array $errors = [])
    {
    }
}
