<?php

namespace Potager\Support;

use Exception;

class Utils
{
    public static function classBasename($class): string
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (!is_string($class))
            throw new Exception('classBasename helper can only handle object or string values');

        $parts = explode('\\', $class);
        return array_pop($parts);
    }
}