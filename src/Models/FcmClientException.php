<?php

declare(strict_types=1);

namespace Mve\FcmPhp\Models;

/**
 * Exception that originated on the client (in this case the server that uses this library). So it has nothing to do with the Google API.
 */
class FcmClientException extends \Exception
{
    
}
