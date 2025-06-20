<?php

namespace Potager\Exceptions;
use Exception;

class HttpException extends Exception
{
    protected static array $defaultMessages = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        408 => 'Request Timeout',
        418 => 'I\'m a teapot',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
    ];

    protected array $headers;

    public function __construct(int $code = 500, ?string $message = null, array $headers = [])
    {
        $message ??= self::$defaultMessages[$code] ?? 'Unknown Error';
        parent::__construct($message, $code);
        $this->headers = $headers;
    }
}