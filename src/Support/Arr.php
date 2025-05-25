<?php

namespace Potager\Support;

class Arr
{
    public static function has(array $array, string $key)
    {
        if (!$key)
            return false;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return false;
            }
            $array = $array[$segment];
        }
        return true;
    }

    public static function get(array $array, string $key, mixed $default = null)
    {
        if (!$key)
            return $array;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }
        return $array;
    }

    // Set a value in a array unsing dot notation
    public static function set(array &$array, string $key, mixed $value)
    {
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $segment = array_shift($keys);
            if (!isset($array[$segment]) || !is_array($array[$segment])) {
                $array[$segment] = [];
            }
            $array = &$array[$segment];
        }
        $array[array_shift($keys)] = $value;
    }

    // Forget a key from an array using dot notation
    public static function forget(array &$array, string $key)
    {
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $segment = array_shift($keys);
            if (!isset($array[$segment]) || !is_array($array[$segment])) {
                return;
            }
            $array = &$array[$segment];
        }
        unset($array[array_shift($keys)]);
    }
}