<?php

declare(strict_types=1);

namespace Mve\FcmPhp\Models;

/**
 * Message that is sent to a specific token.
 */
class TokenMessage extends Message
{
    /**
     * @param string $token The registration token.
     */
    function __construct(protected int $id, protected string $token, protected string $body, protected string $title)
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
