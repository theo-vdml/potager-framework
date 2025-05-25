<?php

namespace Potager\Grape\Validators;

use Potager\Grape\Enums\ArrayValidationMode;
use Potager\Grape\Exceptions\SingleValidationException;
use Potager\Grape\Exceptions\ValidationException;
use Potager\Grape\FieldContext;

class ArrayValidator extends GrapeType
{

    private GrapeType|null $itemValidator;

    private ArrayValidationMode $validationMode;

    public function __construct($itemValidator = null, ArrayValidationMode $validationMode = ArrayValidationMode::FailFast)
    {
        $this->itemValidator = $itemValidator;
        $this->validationMode = $validationMode;

        $this->rules[] = function (mixed $value): array {
            if (!is_array($value))
                throw new SingleValidationException('Value must be an array');
            return (array) $value;
        };
    }

    public function min(int $min): static
    {
        $this->rules[] = function (array $value) use ($min): array {
            if (count($value) < $min)
                throw new SingleValidationException("Array length must not be under {$min}");
            return $value;
        };
        return $this;
    }

    public function max(int $max): static
    {
        $this->rules[] = function (array $value) use ($max): array {
            if (count($value) > $max)
                throw new SingleValidationException("Array length must not exceed {$max}");
            return $value;
        };
        return $this;
    }

    public function empty(): static
    {
        $this->rules[] = function (array $value): array {
            if (count($value) !== 0)
                throw new SingleValidationException("Array must be empty");
            return $value;
        };
        return $this;
    }

    public function notEmpty(): static
    {
        $this->rules[] = function (array $value): array {
            if (count($value) === 0)
                throw new SingleValidationException("Array must not be empty");
            return $value;
        };
        return $this;
    }

    public function distinct(mixed $keys = null): static
    {
        if ($this->itemValidator instanceof SchemaValidator) {
            if (isset($keys) && (is_array($keys) || is_string($keys))) {
                if (is_array($keys)) {
                    $this->rules[] = function (array $value) use ($keys): array {
                        $combos = array_map(fn($item) => array_map(fn($key) => $item[$key] ?? '', $keys), $value);
                        if (count($combos) !== count(array_unique($combos, SORT_REGULAR)))
                            throw new SingleValidationException("Array elements must be distinct");
                        return $value;
                    };
                    return $this;
                } else if (is_string($keys)) {
                    $this->rules[] = function (array $value) use ($keys): array {
                        $items = array_map(fn($item) => $item[$keys] ?? null, $value);
                        if (count($items) !== count(array_unique($items)))
                            throw new SingleValidationException("Array elements must be distinct");
                        return $value;
                    };
                    return $this;
                }
            }
        }
        $this->rules[] = function (array $value): array {
            if (count($value) !== count(array_unique($value, SORT_REGULAR)))
                throw new SingleValidationException("Array elements must be distinct");
            return $value;
        };
        return $this;
    }

    public function validate($value, ?FieldContext $ctx = null)
    {

        if (!is_array($value) || !$this->itemValidator)
            return parent::validate($value);

        $sanitized = [];
        $errors = [];

        foreach ($value as $key => $item) {
            try {
                $sanitized[$key] = $this->itemValidator->validate($item);
            } catch (ValidationException $e) {
                switch ($this->validationMode) {
                    case ArrayValidationMode::FailFast:
                        throw new ValidationException([$key => $e->getErrors()]);
                    case ArrayValidationMode::CollectErrors:
                        $errors[$key] = $e->getErrors();
                        break;
                    case ArrayValidationMode::DropInvalid:
                        break;
                }
            }
        }

        if ($this->validationMode === ArrayValidationMode::CollectErrors && !empty($errors)) {
            throw new ValidationException($errors);
        }

        $sanitized = parent::validate($sanitized);

        return $sanitized;
    }

}