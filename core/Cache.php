<?php
/**
 * Simple file-based cache
 * Works on shared hosting (no Redis/Memcached needed)
 * Compatible with PHP 7.4+
 */
class Cache
{
    private static $dir = '';

    public static function init(string $dir): void
    {
        self::$dir = $dir;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Get a cached value, or null if missing/expired
     *
     * @return mixed
     */
    public static function get(string $key)
    {
        $file = self::path($key);
        if (!file_exists($file)) return null;

        $data = @unserialize(file_get_contents($file));
        if ($data === false || $data['expires'] < time()) {
            @unlink($file);
            return null;
        }

        return $data['value'];
    }

    /**
     * Store a value in the cache
     *
     * @param mixed $value
     */
    public static function set(string $key, $value, int $ttl = 300): void
    {
        if (self::$dir === '') return;
        file_put_contents(
            self::path($key),
            serialize(['value' => $value, 'expires' => time() + $ttl]),
            LOCK_EX
        );
    }

    /**
     * Get a cached value or compute and store it
     *
     * @return mixed
     */
    public static function remember(string $key, int $ttl, callable $callback)
    {
        $value = self::get($key);
        if ($value !== null) return $value;

        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }

    /**
     * Delete a specific cache entry
     */
    public static function forget(string $key): void
    {
        $file = self::path($key);
        if (file_exists($file)) @unlink($file);
    }

    /**
     * Clear all cache files
     */
    public static function flush(): void
    {
        if (self::$dir === '') return;
        foreach (glob(self::$dir . '/*.cache') as $file) {
            @unlink($file);
        }
    }

    private static function path(string $key): string
    {
        return self::$dir . '/' . md5($key) . '.cache';
    }
}
