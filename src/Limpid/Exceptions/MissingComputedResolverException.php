<?php

namespace Potager\Limpid\Exceptions;

use Exception;

class MissingComputedResolverException extends Exception
{
    public function __construct(string $model, string $property, string $method)
    {
        parent::__construct(
            "Missing resolver method '{$method}()' for computed property '{$property}' in model '{$model}'. " .
            "Ensure the method exists and is public."
        );
    }
}
