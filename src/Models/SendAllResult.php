<?php

declare(strict_types=1);

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
    function __construct(private array $sent = [], private array $unregistered = [], private array $errors = [])
    {
    }

    /**
     * Add a successfully sent message to the result.
     */
    public function addToSent(int $messageId, string $firebaseId): void
    {
        $this->sent[$messageId] = $firebaseId;
    }

    /**
     * Add an unregistered message to the result.
     */
    public function addToUnregistered(int $messageId, FcmError $fcmError): void
    {
        $this->unregistered[$messageId] = $fcmError;
    }

    /**
     * Add a message that resulted in an error to the result.
     */
    public function addToErrors(int $messageId, FcmError $fcmError): void
    {
        $this->errors[$messageId] = $fcmError;
    }

    /**
     * Get the sent message and Firebase Ids.
     * 
     * @return array<int, string> An array where the key is the message ID and the value the Firebase ID.
     */
    public function getSent(): array
    {
        return $this->sent;
    }

    /**
     * Get the messages with unregistered tokens.
     * 
     * @return array<int, FcmError> An array where the key is the message ID and the value a FcmError.
     */
    public function getUnregistered(): array
    {
        return $this->unregistered;
    }

    /**
     * Get the messages with errors.
     * 
     * @return array<int, FcmError> An array where the key is the message ID and the value a FcmError.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

}
