<?php

namespace Potager\Limpid\Exceptions;

use Exception;

class InvalidColumnMethodException extends Exception
{
    public static function prepareMethodNotCallable(string $property, string $method): self
    {
        return new self("The 'prepare' method '{$method}' set on property '{$property}' must be defined and a public non-static method.");
    }

    public static function consumeMethodNotCallable(string $property, string $method): self
    {
        return new self("The 'consume' method '{$method}' set on property '{$property}' must be defined and a public non-static method.");
    }
}