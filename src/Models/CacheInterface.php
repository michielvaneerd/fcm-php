<?php

declare(strict_types=1);

namespace Mve\FcmPhp\Models;

/**
 * Interface for simple caching functionality. Used to store access tokens.
 */
interface CacheInterface
{
    /**
     * Gets a key from the cache.
     * 
     * @return mixed Return the value for this key if it exists and is not expired yet.
     */
    public function get(string $key): mixed;

    /**
     * Puts a key into the cache.
     * 
     * @param string $key The key to set.
     * @param mixed $value The value to set.
     * @param int $ttl The time-to-live in seconds.
     */
    public function put(string $key, mixed $value, int $ttl = 10): void;

    /**
     * Removes all items from the cache.
     */
    public function flush(): void;

    /**
     * Removes an item from the cache.
     */
    public function forget(string $key): void;
}
