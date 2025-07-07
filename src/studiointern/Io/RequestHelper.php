<?php

namespace studiointern\Io;

class RequestHelper
{
    public static function filterInput(array $pars, string $method = ''): array
    {
        $ret = [];
        $method = ($method) ?: $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'POST':
                $filter_method = INPUT_POST;
                break;
            case 'GET':
            default:
                $filter_method = INPUT_GET;
                break;
        }

        foreach ($pars as $key => $filter) {
            $ret[$key] = filter_input($filter_method, $key, ...$filter);
        }
        return $ret;
    }
}
