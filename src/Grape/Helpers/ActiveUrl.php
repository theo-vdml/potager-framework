<?php

namespace Potager\Grape\Helpers;

class ActiveUrl
{
    public static function validate(string $string): bool
    {
        if (!Url::validate($string))
            return false;
        $headers = @get_headers($string);
        return $headers && strpos($headers[0], '200' !== false);
    }
}