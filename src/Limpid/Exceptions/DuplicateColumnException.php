<?php

namespace Potager\Limpid\Exceptions;

use Exception;

class DuplicateColumnException extends Exception
{
    public function __construct(string $model, string $property)
    {
        parent::__construct("Model '{$model}' defines a duplicate column for property '{$property}'. Each #[Column] must map to a unique property.");
    }
}
