<?php

namespace Mve\FcmPhp\Models;

class TopicMessage extends Message
{
    function __construct(private readonly int $id, private readonly string $topic, private readonly string $body, private readonly string $title)
    {
        parent::__construct($id, $body, $title);
    }

    public function toArray(): array
    {
        $message = parent::toArray();
        $message['message']['topic'] = $this->topic;
        return $message;
    }
}
