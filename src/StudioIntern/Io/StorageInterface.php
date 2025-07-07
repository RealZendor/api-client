<?php

namespace StudioIntern\Io;

interface StorageInterface
{
    public function __construct(array $config);
    public function getValue(string $param_name);
    public function setValue(string $param_name, $param_value);
    public function deleteValue(string $param_name);
}
