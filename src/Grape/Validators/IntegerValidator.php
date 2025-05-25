<?php

namespace Potager\Grape\Validators;

use Potager\Grape\Exceptions\SingleValidationException;
use Potager\Grape\FieldContext;
use Potager\Grape\Traits\CanBeUnique;
use Potager\Grape\Validators\NumberValidator;

class IntegerValidator extends NumberValidator
{
    use CanBeUnique;

    public function __construct(bool $strict)
    {
        $this->rules[] = function (FieldContext $ctx) use ($strict) {
            $value = $ctx->getValue();
            if ($strict && !is_int($value))
                $ctx->report('{{ field }} must be an integer', 'integer');
            else if (!$strict && (!is_numeric($value) || intval($value) != $value))
                $ctx->report('{{ field }} must be an integer', 'integer');
            else
                $ctx->mutate((int) intval($value));
        };

        parent::__construct();
    }
}