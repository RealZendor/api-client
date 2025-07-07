<?php

namespace studiointern\Api;

class ApiPlainObject
{
    private $success = false;
    private $code    = 0;
    private $locale  = '';
    private $message = "OK";
    private $data    = [];

    public function __construct(array $data = [])
    {
        if (0 < count($data))
        {
            foreach ($data as $key => $value)
            {
                $this->$key = $value;
            }
        }
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __get($name)
    {
        return $this->$name;
    }
}
