<?php


if (!function_exists('path')) {
    function path(?string $path = null)
    {
        if (!defined('BASE_PATH'))
            define('BASE_PATH', dirname(dirname(__DIR__)));

        if (!$path)
            return BASE_PATH;

        $prefix = $path[0];
        $relative_path = substr($path, 1);

        switch ($prefix) {
            case '@':
                return rtrim(BASE_PATH . '/.core/' . ltrim($relative_path, '/'), '/');
            case '#':
                return rtrim(BASE_PATH . '/app/' . ltrim($relative_path, '/'), '/');
            case '~':
                return rtrim(BASE_PATH . '/public/' . ltrim($relative_path, '/'), '/');
            default:
                return rtrim(BASE_PATH . '/' . ltrim($path, '/'), '/');
        }

    }
}