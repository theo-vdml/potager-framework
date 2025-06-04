<?php

namespace Potager\Limpid\Exceptions;

use Exception;

class MultiplePrimaryKeyException extends Exception
{
    public function __construct(string $model, string $existing, string $conflict)
    {
        parent::__construct("Model {$model} cannot have multiple primary keys. Found both '{$existing}' and '{$conflict}' marked as primary.");
    }
}