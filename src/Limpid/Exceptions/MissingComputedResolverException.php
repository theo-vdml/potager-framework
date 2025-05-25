<?php

namespace Potager\Limpid\Exceptions;

use Exception;

class MissingComputedResolverException extends Exception
{
    public function __construct(string $model, string $property, string $method)
    {
        parent::__construct("Missing computed resolver method for '{$property}' in model '{$model}'. Expected method: $method()");
    }
}
