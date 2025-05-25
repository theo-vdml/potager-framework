<?php

namespace Potager\Grape\Exceptions;

class ValidationException extends \Exception
{
    protected array $errors;

    public function __construct(string|array $error)
    {
        // You can either pass the errors as a JSON-encoded string or directly as an array
        $this->errors = is_array($error) ? $error : [$error];
        parent::__construct('Validation failed');
    }

    /**
     * Get all validation errors.
     * 
     * @return array
     */
    public function getErrors(): mixed
    {
        return $this->errors;
    }

    /**
     * Get validation errors as a JSON string.
     * 
     * @return string
     */
    public function getJSONErrors(): string
    {
        return json_encode($this->errors);
    }

    /**
     * Get a single error message by the field/key.
     * 
     * @param string $key
     * @return string|null
     */
    public function getErrorByKey(string $key): ?string
    {
        return $this->errors[$key] ?? null;
    }

    /**
     * Check if a specific key has an error.
     * 
     * @param string $key
     * @return bool
     */
    public function keyHasError(string $key): bool
    {
        return isset($this->errors[$key]);
    }

    /**
     * Get all error keys.
     * 
     * @return array
     */
    public function getErrorKeys(): array
    {
        return array_keys($this->errors);
    }

    /**
     * Get error count.
     * 
     * @return int
     */
    public function getErrorCount(): int
    {
        return count($this->errors);
    }

    /**
     * Check if the validation has no errors.
     * 
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->errors);
    }

    /**
     * Convert the errors to a human-readable string.
     * 
     * @return string
     */
    public function __toString(): string
    {
        return implode("\n", array_map(fn($key, $message) => "$key: $message", array_keys($this->errors), $this->errors));
    }
}
