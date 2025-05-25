<?php

namespace Potager\Grape\Validators;

use Potager\Grape\ValidationResult;
use Exception;
use Potager\Grape\Exceptions\InvalidSchemaException;
use Potager\Grape\Exceptions\SingleValidationException;
use Potager\Grape\Exceptions\ValidationException;
use Potager\Grape\FieldContext;
use Potager\Grape\Validators\GrapeType;

class SchemaValidator extends GrapeType
{

    protected ?array $properties = null;

    public function __construct(?array $properties = null)
    {
        if ($properties) {
            $this->validateProperties($properties);
            $this->properties = $properties;
        }

        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if (!is_array($value))
                $ctx->report('Value must be an array', 'schema');
            else
                $ctx->mutate((array) $value);
        };
    }

    protected function validateProperties(array $properties)
    {
        $this->ensureArrayIsAssociative($properties);
        $this->ensurePropertiesAreGrapeTypes($properties);
    }

    protected function ensureArrayIsAssociative(array $array): void
    {
        if (!is_array($array) || array_is_list($array)) {
            throw new InvalidSchemaException("Schema must be an associative array.");
        }
    }

    protected function ensurePropertiesAreGrapeTypes(array $properties)
    {
        foreach ($properties as $value) {
            if (!$value instanceof GrapeType) {
                $type = is_object($value) ? get_class($value) : gettype($value);
                throw new InvalidSchemaException("Invalid schema value of type: $type");
            }
        }
    }

    public function getProperties(): array
    {
        return $this->properties ?? [];
    }


    public function validate($value, ?FieldContext $ctx = null)
    {
        $raw = $value;

        // Create  a FieldContext if not given (means that we are at the root of the validation)
        if (!$ctx)
            $ctx = new FieldContext($value);

        if (!$this->nullable && $value === null)
            $ctx->report('{{ field }} cannot be null', 'not_nullable');

        if ($value === null)
            return $value;

        foreach ($this->rules as $rule) {
            if ($ctx->isValid())
                $rule($ctx);
        }

        if (!$ctx->isValid())
            return ["messages" => $ctx->getMessages(), "sanitized" => $ctx->getSanitized()];

        if (!$this->getProperties())
            $ctx->sanitize();

        // Foreach property of the schema, attemps validaton
        foreach ($this->getProperties() as $key => $validator) {

            // Created a nested FieldContext for the key
            $nestedCtx = $ctx->getNestedContext($key);

            // Check if the key is required on the schema
            $required = $validator->isRequired();

            // Check if the key is missing in the given data
            if (!array_key_exists($key, $value)) {

                // If it was required, report an error
                if ($required)
                    $nestedCtx->report("{{ field }} is required.", "required");

                // Skip
                continue;
            }

            // Run the validator
            $validator->validate($value[$key], $nestedCtx);

        }

        $messages = $ctx->getMessages();
        $sanitized = $ctx->getSanitized();
        $valid = count($messages) ? false : true;

        $result = new ValidationResult($valid, $raw, $valid ? $sanitized : [], $messages);

        return $result;
    }

    public function validateOrFail($value)
    {
        $result = $this->validate($value);
        if (!$result['valid'])
            throw new ValidationException($result['messages']);
        return $result['sanitized'];
    }

    public function with_key(string $key)
    {
        $this->rules[] = function ($value) use ($key) {

            if (!array_key_exists($key, $value))
                throw new SingleValidationException("{$key} key must exist.");
            return $value;
        };
        return $this;
    }

}