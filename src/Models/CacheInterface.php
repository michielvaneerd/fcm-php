<?php

declare(strict_types=1);

namespace Mve\FcmPhp\Models;

interface CacheInterface
{
    public function get(string $key): mixed;

    public function put(string $key, mixed $value, ?int $ttl = 10): void;

    public function pull(string $key): mixed;

    public function flush(): mixed;

    public function has(string $key): bool;
}
