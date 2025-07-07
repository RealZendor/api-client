<?php

namespace StudioIntern\Io;

class FileStorage implements StorageInterface
{
    private array $config = [];

    private const ERROR_KEY_EMPTY = 'Key is empty';

    public function __construct(array $config = [])
    {
        $this->config['storage_path'] = $config['storage_path'];
        $this->checkStoragePathOutsideWebroot();
        if (!file_exists($this->config['storage_path'])) {
            if (false === mkdir($this->config['storage_path'], 0777, true)) {
                throw new \Exception('Failed to create storage path: ' . $this->config['storage_path']);
            }
        }
        $this->purgeOldStorage();
    }

    public function getValue(string $key)
    {
        if (empty($key)) {
            throw new \Exception(self::ERROR_KEY_EMPTY);
        }
        $hash = $this->hashKey($key);
        $file_path = $this->config['storage_path'] . '/' . $hash;
        if (file_exists($file_path)) {
            return unserialize(file_get_contents($file_path));
        }
        return null;
    }

    public function setValue(string $key, $value)
    {
        if (empty($key)) {
            throw new \Exception(self::ERROR_KEY_EMPTY);
        }
        // hash the key to make sure it's a valid filename
        $hash = $this->hashKey($key);
        $file_path = $this->config['storage_path'] . '/' . $hash;
        file_put_contents($file_path, serialize($value));
    }

    public function deleteValue(string $key)
    {
        if (empty($key)) {
            throw new \Exception(self::ERROR_KEY_EMPTY);
        }
        $hash = $this->hashKey($key);
        $file_path = $this->config['storage_path'] . '/' . $hash;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    private function checkStoragePathOutsideWebroot()
    {
        // storage path must be outside webroot
        if (strpos(\realpath($this->config['storage_path']), $_SERVER['DOCUMENT_ROOT']) === 0) {
            throw new \Exception('Storage path is inside webroot which is not allowed.');
        }
    }

    private function purgeOldStorage(int $minutes = 120)
    {
        $files = glob($this->config['storage_path'] . '/*');
        foreach ($files as $file) {
            if (filemtime($file) < time() - $minutes * 60) {
                unlink($file);
            }
        }
    }

    private function hashKey(string $key): string
    {
        return hash('md5', $key);
    }
}
