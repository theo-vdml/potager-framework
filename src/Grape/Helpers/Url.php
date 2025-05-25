<?php

namespace Potager\Grape\Helpers;

class Url
{
    public static function validate(string $str): bool
    {
        if (!is_string($str))
            return false;
        return filter_var($str, FILTER_VALIDATE_URL);
    }
}