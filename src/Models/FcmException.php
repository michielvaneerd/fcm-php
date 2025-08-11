<?php

declare(strict_types=1);

namespace Mve\FcmPhp\Models;

/**
 * Exception with specific information that is from the Google Firebase API.
 */
class FcmException extends \Exception
{
    /**
     * @param FcmError $fcmError The FcmError instance.
     * @param ?SendAllResult $sendAllResult Optional SendAllResult instance, will only be defined when sending multiple messages. This way you can see which messages have already been sent and which one had errors.
     */
    public function __construct(
        public readonly FcmError $fcmError,
        public readonly ?SendAllResult $sendAllResult = null,
        \Throwable $previous = null
    ) {
        parent::__construct($this->fcmError->message, $this->fcmError->code, $previous);
    }
}
