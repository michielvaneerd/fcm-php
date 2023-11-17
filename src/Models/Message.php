<?php

namespace Mve\FcmPhp\Models;

abstract class Message
{

    function __construct(private int $id, private string $body, private string $title)
    {
    }

    public function getId()
    {
        return $this->id;
    }

    public function toArray(): array
    {
        $message = [
            'notification' => [
                'body' => $this->body,
                'title' => $this->title
            ]
        ];
        return ['message' => $message];
    }
}
