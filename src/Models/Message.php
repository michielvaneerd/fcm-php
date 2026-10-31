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

    private ?string $title;
    private ?string $body;
    private ?array $data;
    private ?string $token;
    private ?string $fid;
    private ?string $topic;
    private ?MessageAndroidConfig $androidConfig;

    /**
     * A unique identifier for this message, for example use a primary key from your database.
     */
    private int $id;

    // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#AndroidConfig
    function __construct(
        int $id,
        ?string $title = null,
        ?string $body = null,
        ?string $topic = null,
        ?string $token = null,
        ?string $fid = null,
        ?array $data = null,
        ?MessageAndroidConfig $androidConfig = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->body = $body;
        $this->topic = $topic;
        $this->token = $token;
        $this->fid = $fid;
        $this->data = $data;
        $this->androidConfig = $androidConfig;
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
     * https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages
     */
    public function toArray(): array
    {
        assert(!empty($this->getKey()), 'Fid, token or topic must be set');
        assert(!(empty($this->notification) && empty($this->data)), 'Data or notification must be set or both');

        $message = [
            $this->getKeyType() => $this->getKey()
        ];

        if (!empty($this->title) || !empty($this->body)) {
            $message['notification'] = [];
            if (!empty($this->title)) {
                $message['notification']['title'] = $this->title;
            }
            if (!empty($this->title) || !empty($this->body)) {
                $message['notification']['body'] = $this->body;
            }
        }

        if (!empty($this->data)) {
            $message['data'] = $this->data;
        }

        if (!empty($this->androidConfig)) {
            $message['android'] = $this->androidConfig->toArray();
        }

        return ['message' => $message];
    }
}
