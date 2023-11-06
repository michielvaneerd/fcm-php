<?php

namespace Mve\FcmPhp\Models;

interface CacheInterface
{
    public static function get(string $key): mixed;

    public static function put(string $key, mixed $value, ?int $ttl = 10): void;

    public static function pull(string $key): mixed;

    public static function flush(): mixed;

    public static function has(string $key): bool;

}
