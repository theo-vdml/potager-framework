<?php

namespace Potager\Support;

/**
 * Class Arr
 *
 * A utility class for manipulating arrays using dot notation.
 *
 * @package Potager\Support
 */
class Arr
{
    /**
     * Determine if an array has the given key using dot notation.
     *
     * @param array $array The array to search.
     * @param string $key The dot-notated key to check.
     * @return bool True if the key exists, false otherwise.
     */
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

    /**
     * Retrieve a value from an array using dot notation.
     *
     * @param array $array The array to search.
     * @param string $key The dot-notated key to retrieve.
     * @param mixed $default The default value to return if the key doesn't exist.
     * @return mixed The value from the array or the default value.
     */
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

    /**
     * Set a value in an array using dot notation.
     *
     * @param array $array The array to modify (passed by reference).
     * @param string $key The dot-notated key to set.
     * @param mixed $value The value to set.
     * @return void
     */
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

    /**
     * Remove a value from an array using dot notation.
     *
     * @param array $array The array to modify (passed by reference).
     * @param string $key The dot-notated key to remove.
     * @return void
     */
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

    /**
     * Wrap a value in an array if it is not already an array.
     *
     * @param mixed $value The value to wrap.
     * @return array The value wrapped in an array.
     */
    public static function wrap(mixed $value): array
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        return $value;
    }
}