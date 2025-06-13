<?php

namespace Potager\ErrorHandler;

use ErrorException;
use Throwable;

class ErrorHandler
{

    private string $environment;
    private array $fatalLevels;

    public function __construct(string $environment = 'dev', ?array $fatalLevels = null)
    {
        $this->environment = $environment;
        $this->fatalLevels = $fatalLevels ?? [
            E_ERROR,
            E_USER_ERROR,
            E_RECOVERABLE_ERROR,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_PARSE
        ];
    }

    public function handleError($errno, $errstr, $errfile, $errline)
    {
        if (error_reporting() & $errno) {
            return false;
        }

        if (in_array($errno, $this->fatalLevels, true)) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }

        error_log("[PHP WARNING] $errstr in $errfile:$errline");

        return true;

    }

    public function handleException(Throwable $exception)
    {
        $this->handleThrowable($exception);
    }

    public function handleThrowable(Throwable $throwable)
    {
        error_log("[" . get_class($throwable) . "] {$throwable->getMessage()} in {$throwable->getFile()}:{$throwable->getLine()}");

        if ($this->environment === 'dev') {
            http_response_code(500);
            echo "<h1>Une erreur est survenue</h1>";
            echo "<pre>{$throwable}</pre>";
        } else {
            http_response_code(500);
            echo "<h1>Une erreur est survenue</h1>";
            echo "<p>Merci de réessayer plus tard.</p>";
        }

        exit(1);
    }
}