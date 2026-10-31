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
    private ?array $androidConfig;

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

    /**
     * Set the Android specific configuration.
     * 
     * @param array $androidConfig The Android config array.
     * 
     * @link https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#AndroidConfig
     */
    public function setAndroidConfig(array $androidConfig)
    {
        $this->androidConfig = $androidConfig;
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
     * https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages
     */
    public function toArray(): array
    {
        assert(!empty($this->getKey()), 'Fid, token or topic must be set');
        assert(!(empty($this->notification) && empty($this->data)), 'Data or notification must be set or both');

        $message = [
            $this->getKeyType() => $this->getKey()
        ];

        if (!empty($this->notification)) {
            $message['notification'] = $this->notification;
        }

        if (!empty($this->data)) {
            $message['data'] = $this->data;
        }

        if (!empty($this->androidConfig)) {
            $message['android'] = $this->androidConfig;
        }

        return ['message' => $message];
    }
}
