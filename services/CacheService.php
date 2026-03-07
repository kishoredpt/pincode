<?php

declare(strict_types=1);

class CacheService
{
    public function __construct(private string $directory, private int $ttl)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
    }

    public function remember(string $key, callable $resolver): mixed
    {
        $file = $this->directory . '/' . sha1($key) . '.phpcache';

        if (is_readable($file) && (filemtime($file) + $this->ttl) > time()) {
            return unserialize((string) file_get_contents($file));
        }

        $value = $resolver();
        file_put_contents($file, serialize($value));
        return $value;
    }
}
