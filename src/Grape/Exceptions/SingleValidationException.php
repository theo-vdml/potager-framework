<?php

namespace Potager\Grape\Exceptions;

use Potager\Grape\Exceptions\ValidationException;

class SingleValidationException extends ValidationException
{
    public function __construct(string $error)
    {
        parent::__construct($error);
    }
}