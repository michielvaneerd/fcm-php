<?php

namespace Mve\FcmPhp\Models;

class FcmException extends \Exception
{
    public function __construct(
        public readonly FcmError $fcmError,
        // Optional SendAllResult (so you can see which items have been processed successfully already before this exception was thrown)
        public readonly ?SendAllResult $sendAllResult = null,
        \Throwable $previous = null
    ) {
        parent::__construct($this->fcmError->message, $this->fcmError->code, $previous);
    }
}
