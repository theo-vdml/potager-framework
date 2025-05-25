<?php

namespace Potager\Grape\Exceptions;

class InvalidSchemaException extends \Exception
{
    public function __construct(string $message = "Invalid schema provided", int $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}