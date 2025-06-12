<?php

namespace Potager\ErrorHandler;

use ErrorException;
use Throwable;

class ErrorHandler
{
    private string $environment;
    private array $handlers = [];

    public function __construct(string $environment = 'dev')
    {
        $this->environment = $environment;
    }

    public function handleException(Throwable $e)
    {

    }

    public function handleError($errno, $errstr, $errfile, $errline)
    {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
}