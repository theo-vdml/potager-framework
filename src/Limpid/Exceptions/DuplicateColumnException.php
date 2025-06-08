<?php

namespace Potager\Limpid\Exceptions;

use Exception;

class DuplicateColumnException extends Exception
{
    public function __construct(string $model, string $firstProperty, string $secondProperty, string $columnName)
    {
        $message = sprintf(
            "Duplicate column detected in model '%s'. The properties '%s' and '%s' both resolve to the column name '%s'. " .
            "Each #[Column] attribute must map to a unique column name in the database. " .
            "Please ensure that each property name and its corresponding column name are unique.",
            $model,
            $firstProperty,
            $secondProperty,
            $columnName
        );

        parent::__construct($message);
    }
}
