<?php

namespace Potager\Grape;

use Potager\Grape\Enums\ArrayValidationMode;
use Potager\Grape\Validators\AcceptedValidator;
use Potager\Grape\Validators\ArrayValidator;
use Potager\Grape\Validators\GrapeType;
use Potager\Grape\Validators\BooleanValidator;
use Potager\Grape\Validators\FloatValidator;
use Potager\Grape\Validators\IntegerValidator;
use Potager\Grape\Validators\MixedValidator;
use Potager\Grape\Validators\NullValidator;
use Potager\Grape\Validators\NumberValidator;
use Potager\Grape\Validators\ObjectValidator;
use Potager\Grape\Validators\SchemaValidator;
use Potager\Grape\Validators\StringValidator;

class Grape
{
    protected static ?\PDO $pdo = null;

    protected static $truthy = [true, "true", "1", 1, "on", "yes", "y", "enable"];
    protected static $falsy = [false, "false", "0", 0, "off", "no", "n", "disable"];

    public static function connectMySQL($dns, $user, $password): void
    {
        static::$pdo = new \PDO($dns, $user, $password);
    }

    public static function getPDO()
    {
        return static::$pdo;
    }

    public static function extendTruthy(array $truthy): void
    {
        $truthy = array_filter($truthy, function ($item): bool {
            return is_bool($item) || is_string($item) || is_numeric($item);
        });

        self::$truthy = array_merge(self::$truthy, $truthy);
    }

    public static function extendFalsy(array $falsy): void
    {
        $falsy = array_filter($falsy, function ($item): bool {
            return is_bool($item) || is_string($item) || is_numeric($item);
        });

        self::$falsy = array_merge(self::$falsy, $falsy);
    }

    public static function dropTruthy(array $truthy): void
    {
        self::$truthy = array_filter(self::$truthy, function ($item) use ($truthy) {
            return !in_array($item, $truthy);
        });
    }

    public static function dropFalsy(array $falsy): void
    {
        self::$falsy = array_filter(self::$falsy, function ($item) use ($falsy) {
            return !in_array($item, $falsy);
        });
    }

    public static function setTruthies(array $truthy): void
    {
        $truthy = array_filter($truthy, function ($item): bool {
            return is_bool($item) || is_string($item) || is_numeric($item);
        });

        self::$truthy = $truthy;
    }

    public static function setFalsies(array $falsy): void
    {
        $falsy = array_filter($falsy, function ($item): bool {
            return is_bool($item) || is_string($item) || is_numeric($item);
        });

        self::$falsy = $falsy;
    }

    public static function getTruthies(): array
    {
        return array_unique([...[true], ...self::$truthy] ?? []);
    }

    public static function getFalsies(): array
    {
        return array_unique([...[false], ...self::$falsy ?? []]);
    }

    public static function isTruthy(mixed $value): bool
    {
        return in_array($value, self::getTruthies(), true) || $value === true;
    }

    public static function isFalsy(mixed $value): bool
    {
        return in_array($value, self::getFalsies(), true) || $value === false;
    }

    public static function number(bool $strict = false): NumberValidator
    {
        return new NumberValidator();
    }

    public static function integer(bool $strict = false): IntegerValidator
    {
        return new IntegerValidator($strict);
    }

    public static function float(bool $strict = false): FloatValidator
    {
        return new FloatValidator($strict);
    }

    public static function string(bool $strict = false): StringValidator
    {
        return new StringValidator($strict);
    }

    public static function boolean(bool $strict = false): BooleanValidator
    {
        return new BooleanValidator($strict);
    }

    public static function accpeted(): AcceptedValidator
    {
        return new AcceptedValidator();
    }

    public static function array($itemValidator = null, ArrayValidationMode $validationMode = ArrayValidationMode::FailFast): ArrayValidator
    {
        return new ArrayValidator($itemValidator, $validationMode);
    }

    public static function schema(?array $schema = null): SchemaValidator
    {
        return new SchemaValidator($schema);
    }

    public static function object(): ObjectValidator
    {
        return new ObjectValidator();
    }

    public static function null(): NullValidator
    {
        return new NullValidator();
    }

    public static function mixed(): MixedValidator
    {
        return new MixedValidator();
    }
}