<?php

declare(strict_types=1);

namespace Mve\Tests;

use Mve\FcmPhp\Models\CacheInterface;

/**
 * Cache for testing to be able to store the access token we receive.
 */
class MyTestCache implements CacheInterface
{
    private static $cache = [];
    private static $cachePath = __DIR__ . '/cache.json';

    private function getCacheJson(): array
    {
        $json = [];
        if (is_readable(self::$cachePath)) {
            try {
                $json = json_decode(file_get_contents(self::$cachePath), true);
            } catch (\Exception $ex) {
                // Do nothing.
            }
        }
        return $json;
    }

    private function putCacheJson(array $json): void
    {
        file_put_contents(self::$cachePath, json_encode($json));
    }

    public function forget(string $key): void
    {
        $json = $this->getCacheJson();
        unset($json[$key]);
        $this->putCacheJson($json);
    }

    public function get(string $key): mixed
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }
        $json = $this->getCacheJson();
        if (array_key_exists($key, $json)) {
            self::$cache[$key] = $json[$key];
            return $json[$key];
        }
        return null;
    }

    public function put(string $key, mixed $value, ?int $ttl = 10): void
    {
        self::$cache[$key] = $value;
        $json = $this->getCacheJson();
        $json[$key] = $value;
        $this->putCacheJson($json);
    }

    public function flush(): void
    {
        $this->putCacheJson([]);
        self::$cache = [];
    }
}
