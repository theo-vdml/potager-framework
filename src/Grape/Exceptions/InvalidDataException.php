<?php

namespace Potager\Grape\Exceptions;

class InvalidDataException extends \Exception
{
    public function __construct(string $message = "Invalid data provided, must be an array", int $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
