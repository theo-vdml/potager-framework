<?php

namespace Potager;

use Potager\Support\Arr;

class Config
{
    protected array $config = [];

    public function __construct(?string $file = null)
    {
        if (!$file)
            $file = path("/config/config.php");

        if (!file_exists($file))
            throw new \Exception("Config file missing at $file");

        $config = require $file;
        if (!is_array($config))
            throw new \Exception("Error in config file at $file");
        $this->config = $config;
    }

    public function get(string $key, mixed $default = null)
    {
        return Arr::get($this->config, $key, $default);
    }
}