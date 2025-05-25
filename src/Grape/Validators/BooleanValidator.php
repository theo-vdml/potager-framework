<?php

namespace Potager\Grape\Validators;

use Potager\Grape\FieldContext;
use Potager\Grape\Grape;

class BooleanValidator extends GrapeType
{
    public function __construct(bool $strict)
    {
        $this->rules[] = function (FieldContext $ctx) use ($strict) {
            $value = $ctx->getValue();
            if ($strict && !is_bool($value))
                $ctx->report("{{ field }} must be a boolean", 'bool');
            else if (!$strict && !Grape::isTruthy($value) && !Grape::isFalsy($value))
                $ctx->report("{{ field }} must be a boolean", 'bool');
            else
                $ctx->mutate((bool) Grape::isTruthy($value));
        };
    }

    public function true(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if ($value === false)
                $ctx->report("{{ field }} must be true", 'true');
        };

        return $this;
    }

    public function false(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if ($value === true)
                $ctx->report("{{ field }} must be false", 'false');
        };

        return $this;
    }
}