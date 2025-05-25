<?php

namespace Potager\Grape\Validators;


use Potager\Grape\FieldContext;

class AcceptedValidator extends GrapeType
{

    public function __construct()
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if (!in_array($value, [true, 1, 'true', '1', 'on'], true))
                $ctx->report('{{ field }} must be accepted', 'accepted');
            return true;
        };
    }

    /**
     * This modifier won't have any effect on the Accepted type
     * @internal 
     * @deprecated Do not use required on accepted type, it's intended by default
     */
    public function required(): static
    {
        return $this;
    }

    /**
     * This modifier won't have any effect on the Accepted type
     * @internal
     * @deprecated Do not use nullable on accepted type, it won't be applied
     */
    public function nullable(): static
    {
        return $this;
    }
}