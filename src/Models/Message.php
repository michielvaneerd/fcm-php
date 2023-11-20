<?php

declare(strict_types=1);

namespace Mve\FcmPhp\Models;

/**
 * Abstract class for messages.
 */
abstract class Message
{

    /**
     * @param int $id A unique identifier for this message, for example use a primary key from your database.
     * @param string $body The message body content.
     * @param string $title The message title.
     */
    function __construct(protected int $id, protected string $body, protected string $title)
    {
    }

    /**
     * Get the id of this message.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Returns the message as an array in the format as expected by the Google API.
     */
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
