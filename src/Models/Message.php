<?php

namespace Mve\FcmPhp\Models;

class Message
{
    function __construct(public readonly int $id, public readonly string $token, public readonly string $body, public readonly string $title)
    {
    }

    public function toArray(): array
    {
        return [
            'message' => [
                'token' => $this->token,
                'notification' => [
                    'body' => $this->body,
                    'title' => $this->title
                ]
            ]
        ];
    }
}
