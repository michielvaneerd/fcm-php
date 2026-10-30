<?php

declare(strict_types=1);

namespace Mve\FcmPhp\Models;

/**
 * Class for messages.
 */
class Message
{

    public const KEY_TYPE_FID = 'fid';
    public const KEY_TYPE_TOKEN = 'token';
    public const KEY_TYPE_TOPIC = 'topic';

    private ?array $notification;
    private ?array $data;
    private ?string $token;
    private ?string $fid;
    private ?string $topic;

    /**
     * @param int $id A unique identifier for this message, for example use a primary key from your database.
     */
    function __construct(private int $id) {}

    public function setToken(string $token)
    {
        $this->token = $token;
    }

    public function setFID(string $fid)
    {
        $this->fid = $fid;
    }

    public function setTopic(string $topic)
    {
        $this->topic = $topic;
    }

    public function setNotification(string $title, string $body)
    {
        $this->notification = [
            'title' => $title,
            'body' => $body
        ];
    }

    public function getKey(): ?string
    {
        return $this->fid ?? $this->token ?? $this->topic;
    }

    public function getKeyType(): ?string
    {
        if (!empty($this->fid)) {
            return self::KEY_TYPE_FID;
        } elseif (!empty($this->token)) {
            return self::KEY_TYPE_TOKEN;
        } elseif (!empty($this->topic)) {
            return self::KEY_TYPE_TOPIC;
        } else {
            return null;
        }
    }

    public function setData(array $data)
    {
        $this->data = $data;
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
        $message = [];

        // Only token OR fid OR topic can be set
        if (!empty($this->token)) {
            $message['token'] = $this->token;
        } elseif (!empty($this->fid)) {
            $message['fid'] = $this->fid;
        } elseif (!empty($this->topic)) {
            $message['topic'] = $this->topic;
        }

        if (!empty($this->notification)) {
            $message['notification'] = $this->notification;
        }

        if (!empty($this->data)) {
            $message['data'] = $this->data;
        }
        return ['message' => $message];
    }
}
