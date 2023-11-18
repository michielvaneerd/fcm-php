<?php

namespace Mve\FcmPhp\Models;

/**
 * Message that is sent to a specific token.
 */
class TokenMessage extends Message
{
    /**
     * @param string $token The registration token.
     */
    function __construct(private int $id, private string $token, private string $body, private string $title)
    {
        parent::__construct($id, $body, $title);
    }

    /**
     * Returns the registration token.
     */
    public function getToken(): string
    {
        return $this->token;
    }

    public function toArray(): array
    {
        $message = parent::toArray();
        $message['message']['token'] = $this->token;
        return $message;
    }
}
