<?php

namespace Mve\FcmPhp\Models;

class TokenMessage extends Message
{
    function __construct(private readonly int $id, private readonly string $token, private readonly string $body, private readonly string $title)
    {
        parent::__construct($id, $body, $title);
    }

    public function getToken()
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
