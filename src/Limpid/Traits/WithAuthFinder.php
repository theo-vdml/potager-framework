<?php

namespace Potager\Limpid\Traits;

use Potager\Limpid\Attributes\Hook;
use Potager\Limpid\Model;
use Potager\Limpid\Contracts\AuthStrategy;
use Potager\Limpid\Strategies\BcryptStrategy;

/**
 * Trait WithAuthFinder
 *
 * Adds authentication-related utilities to a model, including:
 * - Configurable user identification via attributes (#[Auth])
 * - Pluggable password hashing strategies (e.g. bcrypt, argon2, or custom methods)
 * - Credential verification with constant-time comparison to avoid timing attacks
 * - Auto-hashing of the password field before saving (via hook)
 *
 * Intended for use with Limpid-based models that require login/authentication logic.
 */
trait WithAuthFinder
{
    /**
     * Indicates if the authentication configuration has been resolved.
     *
     * @var bool
     */
    protected static bool $authFinderBooted = false;

    /**
     * The list of fields used to identify users (e.g. ['email', 'username']).
     *
     * @var array
     */
    protected static array $authIdentifiers;

    /**
     * The name of the password attribute on the model.
     *
     * @var string
     */
    protected static string $authPasswordAttribute;

    /**
     * The hashing strategy used to hash and verify passwords.
     *
     * @var AuthStrategy
     */
    protected static AuthStrategy $authStrategy;


    /**
     * Returns the configured user identifier fields.
     *
     * @return array
     */
    public static function getAuthIdentifiers(): array
    {
        static::assertAuthFinderBooted();
        return static::$authIdentifiers;
    }

    /**
     * Returns the configured password attribute.
     *
     * @return string
     */
    public static function getAuthPasswordAttribute(): string
    {
        static::assertAuthFinderBooted();
        return static::$authPasswordAttribute;
    }

    /**
     * Returns the configured password hashing strategy.
     *
     * @return AuthStrategy
     */
    public static function getAuthStrategy(): AuthStrategy
    {
        static::assertAuthFinderBooted();
        return static::$authStrategy;
    }


    /* 
     * ========================================================================
     * AUTHENTICATION CORE LOGIC
     * ------------------------------------------------------------------------
     * These methods implement the core authentication functionality:
     * - Locating users via identifier(s)
     * - Verifying credentials securely using a pluggable hashing strategy
     * - Auto-hashing password values before saving to the database
     * - Providing common helpers like `attempt()`
     * ======================================================================== */


    /**
     * Finds a user by any of the configured identifiers (e.g. email, username).
     *
     * @param string $identifier The user input identifier (email, username, etc.)
     * @return static|null The user model instance if found, null otherwise
     */
    public static function findForAuth(string $identifier): ?static
    {
        static::assertAuthFinderBooted();

        foreach (static::$authIdentifiers as $column) {
            $user = static::findBy($column, $identifier);
            if ($user) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Verifies a plain password against a hashed one using the configured strategy.
     *
     * @param string $plain The plaintext password
     * @param string $hashed The hashed password
     * @return bool True if valid, false otherwise
     */
    public static function verifyPlainPasswordAgainstHash(string $plain, string $hashed): bool
    {
        static::assertAuthFinderBooted();
        return static::$authStrategy->verify($plain, $hashed);
    }

    /**
     * Verifies if the given plain password matches the model's password attribute.
     *
     * @param string $plain The plaintext password
     * @param string|null $hashed Optional hash to check against (defaults to model's password field)
     * @return bool True if valid, false otherwise
     */
    public function isPasswordValid(string $plain, ?string $hashed = null): bool
    {
        static::assertAuthFinderBooted();

        // If no hash provided, use the model's password attribute
        if ($hashed === null) {
            $hashed = $this->{static::$authPasswordAttribute};
        }

        return static::$authStrategy->verify($plain, $hashed);
    }

    /**
     * Verifies user credentials by checking the identifier and password.
     * Uses constant-time password verification to prevent timing attacks.
     *
     * @param string $identifier User identifier (email, username, etc.)
     * @param string $password Plain text password
     * @return static|null User model if credentials are valid, null otherwise
     */
    public static function verifyCredentials(string $identifier, string $password): ?static
    {
        static::assertAuthFinderBooted();

        $user = static::findForAuth($identifier);

        // Use a dummy hash to ensure constant-time verification
        $dummyHash = static::$authStrategy->hash('dummy_password');
        $hash = $user ? $user->{static::$authPasswordAttribute} : $dummyHash;

        if (static::$authStrategy->verify($password, $hash) && $user) {
            return $user;
        }

        return null;
    }

    /**
     * Alias for verifyCredentials() for clearer semantics.
     *
     * @param string $identifier User identifier (email, username, etc.)
     * @param string $password Plain text password
     * @return static|null User model if credentials are valid, null otherwise
     */
    public static function attempt(string $identifier, string $password): ?static
    {
        return static::verifyCredentials($identifier, $password);
    }

    /**
     * Hook that hashes the password before saving the user model.
     * This will be triggered automatically by the framework before saving.
     * 
     * @param Model $model The model being saved (injected via the hook system)
     */
    #[Hook('beforeSave')]
    protected function hashPasswordBeforeSave(Model $model): void
    {
        static::assertAuthFinderBooted();

        if ($model->isDirty(static::$authPasswordAttribute)) {
            $password = $model->{static::$authPasswordAttribute};
            $model->{static::$authPasswordAttribute} = static::$authStrategy->hash($password);
        }
    }


    /* 
     * ========================================================================
     * AUTH CONFIGURATION RESOLUTION
     * ------------------------------------------------------------------------
     * These methods handle resolving, validating, and applying authentication
     * configuration such as identifiers, password field, and hashing strategy.
     * Intended to be used at boot time to prepare the model for auth ops.
     * ======================================================================== */


    /**
     * Ensures that the authentication configuration is loaded.
     *
     * Called before performing any auth-related actions.
     *
     * @return void
     */
    public static function assertAuthFinderBooted(): void
    {
        if (!static::$authFinderBooted) {
            static::resolveAuthFinderConfig();
        }
    }


    /**
     * Resolves and initializes the authentication configuration for the model.
     *
     * This method:
     * - Ensures the trait is only used in a class extending `Model`
     * - Loads user-defined configuration via `authFinderConfig()` if defined
     * - Validates and sets the identifier fields (e.g. email, username)
     * - Validates and sets the password column
     * - Validates and initializes the hashing strategy
     *
     * After successful validation, it assigns the resolved configuration
     * to the static properties `$authIdentifiers`, `$authPasswordAttribute`,
     * and `$authStrategy`.
     *
     * @throws \LogicException if the trait is used on a non-Model class
     * @throws \InvalidArgumentException if any part of the configuration is invalid
     */
    protected static function resolveAuthFinderConfig(): void
    {
        static::assertUsedOnModel();

        $config = static::resolveUserConfig();

        $identifiers = static::resolveAndValidateIdentifiers($config);
        $passwordAttr = static::resolveAndValidatePassword($config);
        $strategy = static::resolveAndValidateStrategy($config);

        static::$authIdentifiers = $identifiers;
        static::$authPasswordAttribute = $passwordAttr;
        static::$authStrategy = $strategy;
        static::$authFinderBooted = true;
    }

    /**
     * Asserts that the trait is only used on subclasses of the base Model.
     *
     * @throws \LogicException if the using class does not extend Model.
     */
    protected static function assertUsedOnModel(): void
    {
        if (!is_subclass_of(static::class, Model::class)) {
            throw new \LogicException("Trait WithAuthFinder can only be used on classes extending Model.");
        }
    }

    /**
     * Loads user-defined authentication config from `authFinderConfig()` if available.
     *
     * @return array The configuration array (empty if none defined).
     * @throws \InvalidArgumentException if `authFinderConfig()` returns a non-array value.
     */
    protected static function resolveUserConfig(): array
    {
        if (method_exists(static::class, 'authFinderConfig')) {
            $config = static::authFinderConfig();
            if (!is_array($config)) {
                throw new \InvalidArgumentException(
                    static::class . "::authFinderConfig() must return an array, " . gettype($config) . " given."
                );
            }
            return $config;
        }

        return [];
    }

    /**
     * Validates and resolves the list of identifier fields used for authentication.
     *
     * @param array $config The user config array.
     * @return array The validated list of identifier column names.
     * @throws \InvalidArgumentException if identifiers are missing, empty, or invalid.
     */
    protected static function resolveAndValidateIdentifiers(array $config): array
    {
        $identifiers = $config['identifiers'] ?? ['email'];

        if (!is_array($identifiers) || empty($identifiers)) {
            throw new \InvalidArgumentException(
                static::class . "::\$authIdentifiers must be a non-empty array of identifier keys."
            );
        }

        foreach ($identifiers as $id) {
            if (!is_string($id) || trim($id) === '') {
                throw new \InvalidArgumentException(
                    static::class . "::\$authIdentifiers must contain non-empty strings."
                );
            }
            if (!static::_hasColumn($id)) {
                throw new \InvalidArgumentException(
                    static::class . "::\$authIdentifiers contains '{$id}' which is not a valid column in " . static::class
                );
            }
        }

        return $identifiers;
    }

    /**
     * Validates and resolves the password column name from config.
     *
     * @param array $config The user config array.
     * @return string The validated password column name.
     * @throws \InvalidArgumentException if the password field is missing or invalid.
     */
    protected static function resolveAndValidatePassword(array $config): string
    {
        $passwordAttr = $config['password'] ?? 'password';

        if (!is_string($passwordAttr) || trim($passwordAttr) === '') {
            throw new \InvalidArgumentException(
                static::class . "::\$authPasswordAttribute must be a non-empty string."
            );
        }

        if (!static::_hasColumn($passwordAttr)) {
            throw new \InvalidArgumentException(
                static::class . "::\$authPasswordAttribute '{$passwordAttr}' is not a valid column in " . static::class
            );
        }

        return $passwordAttr;
    }

    /**
     * Validates and resolves the hashing strategy for password security.
     *
     * @param array $config The user config array.
     * @return AuthStrategy A validated strategy instance.
     * @throws \InvalidArgumentException if the strategy is missing, invalid, or not secure.
     */
    protected static function resolveAndValidateStrategy(array $config): AuthStrategy
    {
        $strategy = $config['strategy'] ?? new BcryptStrategy();

        if (!$strategy instanceof AuthStrategy) {
            $type = is_object($strategy) ? get_class($strategy) : gettype($strategy);
            throw new \InvalidArgumentException(
                static::class . "::\$authStrategy must be an instance of AuthStrategy, {$type} given."
            );
        }

        $testInput = bin2hex(random_bytes(16));
        $hashed = $strategy->hash($testInput);
        if (!$strategy->verify($testInput, $hashed)) {
            throw new \InvalidArgumentException(
                static::class . "::\$authStrategy '" . get_class($strategy) . "' is not a valid hashing strategy."
            );
        }

        return $strategy;
    }
}

