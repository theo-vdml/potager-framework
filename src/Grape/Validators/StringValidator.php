<?php



namespace Potager\Grape\Validators;


use Potager\Grape\FieldContext;
use Potager\Grape\Helpers\ActiveUrl;
use Potager\Grape\Helpers\CreditCard;
use Potager\Grape\Helpers\Json;
use Potager\Grape\Helpers\MobilePhone;
use Potager\Grape\Helpers\Url;
use Potager\Grape\Helpers\IP;
use Potager\Grape\Traits\CanBeUnique;

class StringValidator extends GrapeType
{

    use CanBeUnique;

    private bool $convertEmptyStringToNull = false;

    public function __construct(bool $strict)
    {
        $convertEmptyStringToNull = $this->convertEmptyStringToNull;
        $this->rules[] = function (FieldContext $ctx) use ($strict, $convertEmptyStringToNull) {
            $value = $ctx->getValue();
            if ($strict && !is_string($value))
                $ctx->report("{{ field }} must be a string.", 'string');
            else if (!$strict && !is_scalar($value))
                $ctx->report("{{ field }} must be a string.", 'string');
            else
                $ctx->mutate((string) $value);
        };
    }


    /**
     * Summary of trim
     * @return StringValidator
     */
    public function trim(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            $value = trim($value);
            $ctx->mutate($value);
        };

        return $this;
    }

    public function lowercase(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            $value = strtolower($value);
            $ctx->mutate($value);
        };

        return $this;
    }

    public function uppercase(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            $value = strtoupper($value);
            $ctx->mutate($value);
        };

        return $this;
    }

    public function min(int $min): static
    {
        $this->rules[] = function (FieldContext $ctx) use ($min) {
            $value = $ctx->getValue();
            if (strlen($value) < $min)
                $ctx->report("{{ field }} must count at least {$min} characters.", 'min');
        };

        return $this;
    }

    public function max(int $max): static
    {
        $this->rules[] = function (FieldContext $ctx) use ($max) {
            $value = $ctx->getValue();
            if (strlen($value) > $max)
                $ctx->report("{{ field }} must not exceed {$max} characters.", 'max');
        };

        return $this;
    }

    public function preffix(string $prefix, bool $caseSensitive = true): static
    {
        $this->rules[] = function (FieldContext $ctx) use ($prefix, $caseSensitive) {
            $value = $ctx->getValue();
            $haystack = $caseSensitive ? $value : strtolower($value);
            $needle = $caseSensitive ? $prefix : strtolower($prefix);
            if (!str_starts_with($haystack, $needle))
                $ctx->report("{{ field }} must starts with {$prefix}", 'preffix');
        };

        return $this;
    }

    public function suffix(string $suffix, bool $caseSensitive = true): static
    {
        $this->rules[] = function (FieldContext $ctx) use ($suffix, $caseSensitive) {
            $value = $ctx->getValue();
            $haystack = $caseSensitive ? $value : strtolower($value);
            $needle = $caseSensitive ? $suffix : strtolower($suffix);
            if (!str_ends_with($haystack, $needle))
                $ctx->report("{{ field }} must ends with {$suffix}", 'suffix');
        };

        return $this;
    }

    public function contains(string $substring, bool $caseSensitive = true): static
    {
        $this->rules[] = function (FieldContext $ctx) use ($substring, $caseSensitive) {
            $value = $ctx->getValue();
            $haystack = $caseSensitive ? $value : strtolower($value);
            $needle = $caseSensitive ? $substring : strtolower($substring);
            if (!str_contains($haystack, $needle))
                $ctx->report("{{ field }} must contain '{$substring}'.", 'contains');
        };

        return $this;
    }

    public function length(int $length): static
    {
        $this->rules[] = function (FieldContext $ctx) use ($length) {
            $value = $ctx->getValue();
            if (strlen($value) !== $length)
                $ctx->report("{{ field }} must be exactly {$length} characters long.", 'length');
        };

        return $this;
    }

    public function alphabetic(bool $allowWhitespaces = true, bool $allowDashes = false, bool $allowUnderscores = false): static
    {
        $this->rules[] = function (FieldContext $ctx) use ($allowWhitespaces, $allowDashes, $allowUnderscores) {
            $value = $ctx->getValue();
            $str = $allowWhitespaces ? preg_replace('/\s+/u', '', $value) : $value;
            $str = $allowDashes ? str_replace('-', '', $str) : $str;
            $str = $allowUnderscores ? str_replace('_', '', $str) : $str;
            if (!ctype_alpha($str))
                $ctx->report("{{ field }} must contain only alphabetic characters.", 'alphabetic');
        };

        return $this;
    }

    public function numeric(bool $allowWhitespaces = true, bool $allowDashes = false, bool $allowUnderscores = false): static
    {
        $this->rules[] = function (FieldContext $ctx) use ($allowWhitespaces, $allowDashes, $allowUnderscores) {
            $value = $ctx->getValue();
            $str = $allowWhitespaces ? preg_replace('/\s+/u', '', $value) : $value;
            $str = $allowDashes ? str_replace('-', '', $str) : $str;
            $str = $allowUnderscores ? str_replace('_', '', $str) : $str;
            if (!ctype_digit($str))
                $ctx->report("{{ field }} must contain only numeric characters.", 'numeric');
        };

        return $this;
    }

    public function alphanumeric(bool $allowWhitespaces = true, bool $allowDashes = false, bool $allowUnderscores = false): static
    {
        $this->rules[] = function (FieldContext $ctx) use ($allowWhitespaces, $allowDashes, $allowUnderscores) {
            $value = $ctx->getValue();
            $str = $allowWhitespaces ? preg_replace('/\s+/u', '', $value) : $value;
            $str = $allowDashes ? str_replace('-', '', $str) : $str;
            $str = $allowUnderscores ? str_replace('_', '', $str) : $str;
            if (!ctype_alnum($str))
                $ctx->report("{{ field }} must contain only alphanumeric characters.", 'alphanumeric');
        };

        return $this;
    }

    public function noWhitespace(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if (preg_match('/\s/', $value))
                $ctx->report("{{ field }} must not contain whitespace.", 'no_whitespaces');
        };

        return $this;
    }

    public function email(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if (!filter_var($value, FILTER_VALIDATE_EMAIL))
                $ctx->report('{{ field }} muts be a valid email', 'email');
        };

        return $this;
    }

    public function phone(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if (!MobilePhone::validate($value))
                $ctx->report("{{ field }} must be a valid mobile phone", 'phone');
        };

        return $this;
    }

    public function json(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if (!Json::validate($value))
                $ctx->report("{{ field }} must be a valid json string", 'json');
        };

        return $this;
    }

    public function url(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if (!Url::validate($value))
                $ctx->report("{{ field }} must be a valid url", 'url');
        };

        return $this;
    }

    public function activeUrl(): static
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if (!ActiveUrl::validate($value))
                $ctx->report("{{ field }} must be an active url", 'active_url');
        };

        return $this;
    }

    public function creditCard(?array $providers = null): static
    {
        $this->rules[] = function (FieldContext $ctx) use ($providers) {
            $value = $ctx->getValue();
            if (!CreditCard::validate($value, $providers))
                $ctx->report("{{ field }} must be a valid credit card", 'credit_card');
        };

        return $this;
    }

    public function ip(?string $version = null): static
    {
        $this->rules[] = function (FieldContext $ctx) use ($version) {
            $value = $ctx->getValue();
            if (!IP::validate($value, $version))
                $ctx->report("{{ field }} must be a valid IP.", 'ip');
        };

        return $this;
    }

    public function empty(bool $ignoreWhitespaces = true)
    {
        $this->rules[] = function (FieldContext $ctx) use ($ignoreWhitespaces) {
            $value = $ctx->getValue();
            $string = $ignoreWhitespaces ? trim($value) : $value;
            if (strlen($string) > 0)
                $ctx->report("{{ field }} must be an empty string", 'empty');
        };

        return $this;
    }

    public function notEmpty()
    {
        $this->rules[] = function (FieldContext $ctx) {
            $value = $ctx->getValue();
            if (strlen($value) === 0)
                $ctx->report("{{ field }} must not be empty", 'empty');
        };

        return $this;
    }

    public function pattern(string $pattern): static
    {
        $this->rules[] = function (FieldContext $ctx) use ($pattern) {
            $value = $ctx->getValue();
            if (!preg_match($pattern, $value))
                $ctx->report("{{ field }} must match the pattern", 'regex');
        };

        return $this;
    }

}