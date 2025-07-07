<?php

namespace StudioIntern\Io;

class SessionStorage implements StorageInterface
{
    private array $config = [];

    private const ERROR_KEY_EMPTY = 'Key is empty';

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function getValue(string $key)
    {
        if (empty($key)) {
            throw new \Exception(self::ERROR_KEY_EMPTY);
        }
        $hk = $this->hashKey($key);
        return $_SESSION[$hk] ?? null;
    }

    public function setValue(string $key, $value)
    {
        if (empty($key)) {
            throw new \Exception(self::ERROR_KEY_EMPTY);
        }
        $hk = $this->hashKey($key);
        $_SESSION[$hk] = $value;
    }

    public function deleteValue(string $key)
    {
        if (empty($key)) {
            throw new \Exception(self::ERROR_KEY_EMPTY);
        }
        $hk = $this->hashKey($key);
        $_SESSION[$hk] = null;
    }

    private function hashKey(string $key): string
    {
        return hash('md5', $key);
    }
}
