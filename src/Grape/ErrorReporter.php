<?php

namespace Potager\Grape;

class ErrorReporter
{
    public array $messages = [];

    public function report(string $field, string $rule, string $message)
    {
        $this->messages[$field] = ["message" => $message, "rule" => $rule];
    }
}