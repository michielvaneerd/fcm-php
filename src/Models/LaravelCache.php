<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use \Mve\FcmPhp\Models\CacheInterface;

class LaravelCache implements CacheInterface
{
    public function get(string $key): mixed
    {
        return Cache::get($key);
    }

    public function put(string $key, mixed $value, ?int $ttl = 10): void
    {
        Cache::put($key, $value, $ttl);
    }

    public function pull(string $key): mixed
    {
        return Cache::pull($key);
    }

    public function flush(): mixed
    {
        Cache::flush();
    }

    public function has(string $key): bool
    {
        return Cache::has($key);
    }
}
