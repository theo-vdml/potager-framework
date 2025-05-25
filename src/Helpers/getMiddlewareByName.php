<?php

function getMiddlewareByName($name)
{
    $file = __DIR__ . "/../../app/Middlewares/" . $name . ".php";
    if (!file_exists($file)) {
        throw new \Exception("Middleware file not found: $file");
    }
    if (!is_readable($file)) {
        throw new \Exception("Middleware file not readable: $file");
    }

    $middlewareFunction = require $file;

    if (!is_callable($middlewareFunction)) {
        throw new \Exception("Middleware file does not return a callable: $file");
    }

    return $middlewareFunction;
}