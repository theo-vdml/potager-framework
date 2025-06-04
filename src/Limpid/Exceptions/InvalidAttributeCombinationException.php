<?php

namespace Potager\Limpid\Exceptions;

use Exception;

class InvalidAttributeCombinationException extends Exception
{
    public function __construct(string $model, string $property)
    {
        parent::__construct(
            "Invalid attribute combination on property '{$property}' in model '{$model}'. " .
            "A property cannot be annotated with both #[Column] and #[Computed]. Choose one or the other."
        );
    }
}
