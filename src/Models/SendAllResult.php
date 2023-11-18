<?php

namespace Mve\FcmPhp\Models;

/**
 * A result for multiple messages. For example used when sending multiple messages to multiple devices.
 */
class SendAllResult
{
    /**
     * @param array<int, string> $sent Successfully sent messages where key = message ID and value = Firebase message ID.
     * @param array<int, FcmError> $unregistered Messages that were sent to tokens that have been unregistered and can therefore be safely removed. The key is the message ID and the value is a FcmError.
     * @param array<int, FcmError> $errors Messages that resulted in errors. The key is the message ID and the value is a FcmError.
     */
    function __construct(public readonly array $sent = [], public readonly array $unregistered = [], public readonly array $errors = [])
    {
    }
}
