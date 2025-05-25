<?php

namespace Potager\Grape\Validators;

use Potager\Grape\FieldContext;

class NullValidator extends GrapeType
{
    public function __construct()
    {

        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if ($value === null)
                $ctx->report('{{ field }} must be null', '');
        };

        $this->nullable();
    }
}
