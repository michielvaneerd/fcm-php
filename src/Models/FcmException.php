<?php

namespace Mve\FcmPhp\Models;

class FcmException extends \Exception
{
    public readonly ?array $content;
    public readonly ?string $errorName;
    public readonly ?string $rawContent;
    public readonly ?SendResult $sendResult;

    public function __construct(string $message, int $code = 0, array $content = null, string $errorName = null, string $rawContent = null, SendResult $sendResult = null, \Throwable $previous = null)
    {
        $this->content = $content;
        $this->errorName = $errorName;
        $this->rawContent = $rawContent;
        $this->sendResult = $sendResult;
        parent::__construct($message, $code, $previous);
    }
}
