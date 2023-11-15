<?php

declare(strict_types=1);

namespace Mve\FcmPhp\Models;

use Psr\Log\LoggerInterface;

trait LoggerTrait
{
    private ?LoggerInterface $logger;

    private function log(string $s)
    {
        if (!empty($this->logger)) {
            $this->logger->debug($s);
        }
    }
}
