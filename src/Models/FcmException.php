<?php

namespace Mve\FcmPhp\Models;

class FcmException extends \Exception
{
    public readonly ?array $content;
    public readonly ?string $errorName;
    public readonly ?string $rawContent;
    public readonly ?SendAllResult $sendAllResult;

    public function __construct(
        string $message,
        int $code = 0, // HTTP status code
        array $content = null, // JSON array of full response
        string $errorName = null, // Short error name, like UNAUTHENTICATED or UNREGISTERED
        string $rawContent = null, // Raw plain content of full response
        SendAllResult $sendAllResult = null, // Optional SendAllResult (so you can see which items have been processed successfully already before this exception was thrown)
        \Throwable $previous = null
    ) {
        $this->content = $content;
        $this->errorName = $errorName;
        $this->rawContent = $rawContent;
        $this->sendAllResult = $sendAllResult;
        parent::__construct($message, $code, $previous);
    }
}
