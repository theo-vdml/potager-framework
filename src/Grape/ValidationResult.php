<?php

namespace Potager\Grape;

class ValidationResult
{

    public function __construct(
        protected bool $valid,
        protected array $raw,
        protected array $sanitized,
        protected array $messages
    ) {
    }

    public function messages()
    {
        return $this->messages;
    }

    public function raw()
    {
        return $this->raw;
    }

    public function sanitized()
    {
        return $this->sanitized;
    }

    public function passes()
    {
        return $this->valid;
    }

    public function failed()
    {
        return !$this->valid;
    }

    public function ok()
    {
        return $this->valid;
    }

    public function hasErrors()
    {
        return !$this->valid;
    }

}