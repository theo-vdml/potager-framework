<?php

namespace Potager\Grape\Validators;

use Potager\Grape\Exceptions\SingleValidationException;
use Potager\Grape\FieldContext;

class ObjectValidator extends GrapeType
{

    public function __construct()
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if (!is_object($value))
                $ctx->report('{{ field }} must be an object', '');
        };

    }

    public function instanceOf(string $class): static
    {
        $this->rules[] = function (FieldContext $ctx) use ($class) {
            $value = $ctx->getValue();
            if (!($value instanceof $class))
                $ctx->report("{{ field }} must be an instance of $class", '');
        };

        return $this;
    }

    public function hasProperty(string $property): static
    {
        $this->rules[] = function (FieldContext $ctx) use ($property) {
            $value = $ctx->getValue();
            if (!property_exists($value, $property))
                $ctx->report("{{ field }} must have property $property", '');
        };

        return $this;
    }

    public function hasMethod(string $method): static
    {
        $this->rules[] = function (FieldContext $ctx) use ($method) {
            $value = $ctx->getValue();
            if (!method_exists($value, $method))
                $ctx->report("{{ field }} must have property $method", '');
        };

        return $this;
    }

    public function implementsInterface($interface): static
    {
        $this->rules[] = function (FieldContext $ctx) use ($interface) {
            $value = $ctx->getValue();
            if (!in_array($interface, class_implements($value)))
                $ctx->report("{{ field }} must implements $interface", '');
        };

        return $this;
    }
}