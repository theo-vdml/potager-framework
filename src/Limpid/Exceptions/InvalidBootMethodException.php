<?php

namespace Potager\Limpid\Exceptions;

use Exception;

class InvalidBootMethodException extends Exception
{
    public function __construct(string $model, string $method)
    {
        parent::__construct("Invalid boot method '{$method}' on model '{$model}'. Boot methods must be public, static, and have no required parameters.");
    }
}
