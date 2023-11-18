<?php

declare(strict_types=1);

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

    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    public function flush(): void
    {
        Cache::flush();
    }
}
