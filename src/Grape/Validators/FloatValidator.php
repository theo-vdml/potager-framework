<?php

namespace Potager\Grape\Validators;

use Potager\Grape\FieldContext;

class FloatValidator extends NumberValidator
{


    public function __construct(bool $strict)
    {
        $this->rules[] = function (FieldContext $ctx) use ($strict) {
            $value = $ctx->getValue();
            if ($strict && !is_float($value))
                $ctx->report("{{ field }} must be a float", '');
            else if (!$strict && !is_numeric($value))
                $ctx->report("{{ field }} must be a float", '');
            else
                $ctx->mutate((float) floatval($value));
        };

        parent::__construct();
    }


    public function round(int $precision = 0, int $mode = PHP_ROUND_HALF_UP): static
    {
        $this->rules[] = function (FieldContext $ctx) use ($precision, $mode) {
            $value = $ctx->getValue();
            $value = round($value, $precision, $mode);
            $ctx->mutate($value);
        };

        return $this;
    }

    public function floor(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            $value = floor($value);
            $ctx->mutate($value);
        };

        return $this;
    }

    public function NaN(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if (!is_nan($value))
                $ctx->report("{{ field }} must be NaN", '');
        };

        return $this;
    }

    public function notNaN(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if (is_nan($value))
                $ctx->report("{{ field }} must not be NaN", '');
        };

        return $this;
    }

    public function without_decimals(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if ($value != floor($value))
                $ctx->report("{{ field }} must not have decimals", '');
        };

        return $this;
    }

}