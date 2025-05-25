<?php

namespace Potager;

class Cache
{
    public static function set(string $key, mixed $data, int $duration = 86400)
    {
        $cacheFile = path("/storage/.cache/{$key}.json");

        $content = [
            "expire" => time() + $duration,
            "data" => $data
        ];

        file_put_contents($cacheFile, json_encode($content));
    }

    public static function get(string $key)
    {
        $cacheFile = path("/storage/.cache/{$key}.json");
        if (!file_exists($cacheFile))
            return null;

        $content = file_get_contents($cacheFile);
        $json = json_decode($content, true);

        if ($json['expire'] < time()) {
            unlink($cacheFile);
            return null;
        }

        return $json['data'];
    }
}