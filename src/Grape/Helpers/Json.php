<?php

namespace Potager\Grape\Helpers;

class Json
{
    public static function validate(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}