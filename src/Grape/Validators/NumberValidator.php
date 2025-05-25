<?php

namespace Potager\Grape\Validators;

use Potager\Grape\Exceptions\SingleValidationException;
use Potager\Grape\FieldContext;
use Potager\Grape\Validators\GrapeType;

class NumberValidator extends GrapeType
{

    public function __construct()
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if (!is_int($value) && !is_float($value))
                $ctx->report("{{ field }} must be a valid interger or float", 'number');
        };
    }

    public function abs(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            $value = abs($value);
            $ctx->mutate($value);
        };

        return $this;
    }


    public function min(int|float $min): static
    {
        $this->rules[] = function (FieldContext $ctx) use ($min) {
            $value = $ctx->getValue();
            if ($value < $min)
                $ctx->report("{{ field }} must be higher than $min", 'min');
        };

        return $this;
    }

    public function max(int|float $max): static
    {
        $this->rules[] = function (FieldContext $ctx) use ($max) {
            $value = $ctx->getValue();
            if ($value > $max)
                $ctx->report("{{ field }} must be lower than $max", 'max');
        };

        return $this;
    }

    public function range(int|float $min, int|float $max): static
    {
        $this->rules[] = function (FieldContext $ctx) use ($min, $max) {
            $value = $ctx->getValue();
            if ($value < $min || $value > $max)
                $ctx->report("{{ field }} must be between $min and $max ", 'range');
        };

        return $this;
    }

    public function zero(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if ($value !== 0)
                $ctx->report('{{ field }} must be zero', 'zero');
        };

        return $this;
    }

    public function nonZero(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if ($value === 0)
                $ctx->report('{{ field }} must not be zero', 'non_zero');
        };

        return $this;
    }

    public function positive(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if ($value < 0)
                $ctx->report('{{ field }} must be positive', 'positive');
        };

        return $this;
    }

    public function negative(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if ($value >= 0)
                $ctx->report('{{ field }} must be negative', 'negative');
        };

        return $this;
    }

    public function odd(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if ($value % 2 === 0)
                $ctx->report('{{ field }} must be odd', 'odd');
        };

        return $this;
    }

    public function even(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if ($value % 2 !== 0)
                $ctx->report('{{ field }} must be even', 'even');
        };

        return $this;
    }

}