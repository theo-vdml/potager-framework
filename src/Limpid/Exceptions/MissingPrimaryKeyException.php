<?php

namespace Potager\Limpid\Exceptions;

use Exception;

class MissingPrimaryKeyException extends Exception
{
    public function __construct()
    {
        $message = 'Missing primary key: The model must have a primary key defined. Either specify a primary key explicitly or ensure that an "id" column is defined as the default primary key.';
        parent::__construct($message);
    }
}
