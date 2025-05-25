<?php

namespace Potager\Grape\Validators;

use Potager\Grape\Exceptions\MissingContextException;
use Potager\Grape\FieldContext;
use Exception;

abstract class GrapeType
{
    protected $rules = [];
    protected $nullable = false;
    protected $required = false;
    protected $key = null;
    protected $name = null;

    public function validate($value, ?FieldContext $ctx = null)
    {
        // If there is no context it means that no schema was wrapping the validator
        if (!$ctx)
            throw new MissingContextException();

        // If it accept null value and the value is null, return without validation.
        if (!$this->nullable && $value === null)
            $ctx->report('{{ field }} cannot be null', 'not_nullable');

        if ($value === null)
            return $value;

        // Execute each rules in the order their where added
        foreach ($this->rules as $rule) {
            if ($ctx->isValid())
                $value = $rule($ctx);
        }
        if ($ctx->isValid())
            $ctx->sanitize();
        // Return the final value after each rules execution
        // return $value;
    }

    public function nullable()
    {
        // Set nullable to true
        $this->nullable = true;
        // Return the GrapeItem instance to allow chaining
        return $this;
    }

    public function required(): static
    {
        // Set required to true
        $this->required = true;
        // Return the GrapeItem instance to allow chaining
        return $this;
    }

    public function name(string $name): static
    {
        // Set the name
        $this->name = $name;
        // Return the GrapeItem instance to allow chaining
        return $this;
    }

    /**
     * @internal
     */
    public function key(string $key): static
    {
        // Set the key
        $this->key = $key;
        // Return the GrapeItem instance to allow chaining
        return $this;
    }

    /**
     * @internal
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

}