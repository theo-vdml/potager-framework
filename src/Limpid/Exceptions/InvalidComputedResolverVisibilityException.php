<?php

namespace Potager\Limpid\Exceptions;

use Exception;

class InvalidComputedResolverVisibilityException extends Exception
{
    public function __construct(string $model, string $property, string $method)
    {
        parent::__construct(
            "Invalid visibility for computed resolver method '{$method}' in model '{$model}' for property '{$property}'. " .
            "Computed resolver methods must be public."
        );
    }
}
