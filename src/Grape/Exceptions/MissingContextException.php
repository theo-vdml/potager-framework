<?php

namespace Potager\Grape\Exceptions;

use LogicException;

class MissingContextException extends LogicException
{
    public function __construct(string $message = "Validation must be wrapped into a Grape::schema() to be used. Grape cannot handle singleton validations.", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}