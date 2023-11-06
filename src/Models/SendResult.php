<?php

namespace Mve\FcmPhp\Models;

/**
 * Result object that contains information about each Message, like whether it has been sent successfully, has an invalid token or has some error.
 */
class SendResult
{
    /**
     * @param int[] $sentIds Message id's that have been sent successfully.
     * @param int[] $invalidIds Message id's that have invalid tokens.
     * @param array<int, FcmException> $errorIds Message id's and exceptions.
     */
    function __construct(public array $sentIds = [], public array $invalidIds = [], public array $errorIds = [])
    {
    }
}
