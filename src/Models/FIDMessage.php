<?php

declare(strict_types=1);

namespace Mve\FcmPhp\Models;

/**
 * Message that is sent to a specific FID (Firebase Installation ID).
 */
class FIDMessage extends Message
{
    /**
     * @param string $fid The FID (Firebase Installation ID).
     */
    function __construct(protected int $id, protected string $fid, protected string $body, protected string $title)
    {
        parent::__construct($id, $body, $title);
    }

    /**
     * Returns the FID (Firebase Installation ID).
     */
    public function getFID(): string
    {
        return $this->fid;
    }

    public function toArray(): array
    {
        $message = parent::toArray();
        $message['message']['fid'] = $this->fid;
        return $message;
    }
}
