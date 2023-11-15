<?php

declare(strict_types=1);

namespace Mve\Tests;

use Mve\FcmPhp\Models\CacheInterface;

class MyTestCache implements CacheInterface
{
    private static $cache = [];

    public function get(string $key): mixed {
        return array_key_exists($key, self::$cache) ? self::$cache[$key] : null;
    }

    public function put(string $key, mixed $value, ?int $ttl = 10): void {
        self::$cache[$key] = $value;
    }

    public function pull(string $key): mixed {
        $value = self::$cache[$key];
        unset(self::$cache[$key]);
        return $value;
    }

    public function flush(): mixed {
        self::$cache = [];
    }

    public function has(string $key): bool {
        return array_key_exists($key, self::$cache);
    }
}