<?php

declare(strict_types=1);

namespace Mve\FcmPhp\Models;

/**
 * Message that is sent to a specific topic.
 */
class TopicMessage extends Message
{
    /**
     * @param string $topic The topic.
     */
    function __construct(private int $id, private string $topic, private string $body, private string $title)
    {
        parent::__construct($id, $body, $title);
    }

    /**
     * Returns the topic.
     */
    public function getTopic(): string
    {
        return $this->topic;
    }

    public function toArray(): array
    {
        $message = parent::toArray();
        $message['message']['topic'] = $this->topic;
        return $message;
    }
}
