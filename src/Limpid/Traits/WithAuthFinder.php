<?php

namespace Potager\Limpid\Traits;

use Potager\Limpid\Attributes\Hook;
use Potager\Limpid\Model;

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
    // Static cache to store the resolved config so it's not recalculated on every call
    protected static ?array $authFinderResolvedConfig = null;

    /**
     * Retrieves the authentication configuration for the model.
     * It merges a default config with the user-defined config from the #[Auth] attribute if present.
     *
     * @return array {
     *   @var array $identifiers Columns used as user identifiers (e.g. email, username)
     *   @var string $password The column name where the hashed password is stored
     *   @var string|array $strategy The hashing/verifying strategy (e.g. 'bcrypt' or ['hashMethod', 'verifyMethod'])
     * }
     */
    protected static function resolveAuthConfig(): array
    {
        // Default config applied if no #[Auth] attribute is defined on the model
        $defaultConfig = [
            'identifiers' => ['email'],
            'password' => 'password',
            'strategy' => 'bcrypt',
        ];

        // Reflect on the current model class to check for #[Auth] attributes
        $reflection = new \ReflectionClass(static::class);
        $attributes = $reflection->getAttributes(\Potager\Limpid\Attributes\Auth::class);

        // If no attribute found, return the default config
        if (count($attributes) === 0) {
            return $defaultConfig;
        }

        // Instantiate the #[Auth] attribute and get user config
        /** @var \Potager\Limpid\Attributes\Auth $auth */
        $auth = $attributes[0]->newInstance();
        $userConfig = $auth->getConfig();

        // Merge user config on top of default config, user settings overwrite defaults
        return array_merge($defaultConfig, $userConfig);
    }


    /**
     * Resolves the hashing and verification strategy based on the config.
     * Supports either a predefined string strategy ('bcrypt', 'argon2') or
     * a custom array with method names defined on the model.
     *
     * @param array $config The auth config returned by resolveAuthConfig()
     * @return array {
     *   @var callable $hash Function to hash plain password
     *   @var callable $verify Function to verify plain password against hash
     * }
     *
     * @throws \InvalidArgumentException if the strategy is unknown or improperly configured
     */
    protected static function resolveStrategy(array $config): array
    {
        $strategy = $config['strategy'];

        // Handle predefined string strategies with native PHP password functions
        if (is_string($strategy)) {
            return match ($strategy) {
                'bcrypt' => [
                    'hash' => fn(string $plain) => password_hash($plain, PASSWORD_BCRYPT),
                    'verify' => fn(string $plain, string $hash) => password_verify($plain, $hash),
                ],
                'argon2' => [
                    'hash' => fn(string $plain) => password_hash($plain, PASSWORD_ARGON2ID),
                    'verify' => fn(string $plain, string $hash) => password_verify($plain, $hash),
                ],
                default => throw new \InvalidArgumentException("Unknown strategy: $strategy"),
            };
        }

        // Handle custom strategy: expects an array of two method names [hashMethod, verifyMethod]
        if (is_array($strategy) && count($strategy) === 2) {
            [$hashMethod, $verifyMethod] = $strategy;

            // Check that both methods exist on the model class
            if (!method_exists(static::class, $hashMethod) || !method_exists(static::class, $verifyMethod)) {
                throw new \InvalidArgumentException("Invalid custom strategy methods on model");
            }

            // Return callables wrapping the model's custom methods
            return [
                'hash' => fn(string $plain) => static::$hashMethod($plain),
                'verify' => fn(string $plain, string $hash) => static::$verifyMethod($plain, $hash),
            ];
        }

        // Strategy config invalid if none of the above match
        throw new \InvalidArgumentException("Invalid strategy configuration");
    }

    /**
     * Returns the full resolved auth config with hashing and verifying callables.
     * Uses cached result if already computed.
     *
     * @return array {
     *   @var array $identifiers
     *   @var string $password
     *   @var callable $hash
     *   @var callable $verifier
     * }
     */
    protected static function getAuthFinderConfig(): array
    {
        // Return the cached config if it was already resolved
        if (static::$authFinderResolvedConfig !== null) {
            return static::$authFinderResolvedConfig;
        }

        // Resolve base config and strategy functions
        $config = static::resolveAuthConfig();
        $strategy = static::resolveStrategy($config);

        // Cache and return the full config with callable hash & verifier functions
        static::$authFinderResolvedConfig = [
            "identifiers" => $config['identifiers'],
            'password' => $config['password'],
            'hash' => $strategy['hash'],
            'verifier' => $strategy['verify'],
        ];
        return static::$authFinderResolvedConfig;
    }

    /**
     * Finds a user by any of the configured identifiers (e.g. email, username).
     *
     * @param string $identifier The user input identifier (email, username, etc.)
     * @return static|null The user model instance if found, null otherwise
     */
    public static function findForAuth(string $identifier): ?static
    {
        $config = static::getAuthFinderConfig();
        $identifiers = $config['identifiers'];

        foreach ($identifiers as $column) {
            $user = static::findBy($column, $identifier);
            if ($user) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Compares a plain text password against a hashed password.
     *
     * @param string $plain Plain text password to check
     * @param string $hashed Stored password hash
     * @return bool True if passwords match, false otherwise
     */
    public static function validatePassword(string $plain, string $hashed): bool
    {
        $verifier = static::getAuthFinderConfig()['verifier'];
        return $verifier($plain, $hashed);
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
        $config = static::getAuthFinderConfig();
        $identifiers = $config['identifiers'];
        $hasher = $config['hash'];
        $verifier = $config['verifier'];
        $passwordColumn = $config['password'];

        // Create a dummy hash so that password_verify is always called, to prevent timing attacks
        $dummyHash = $hasher('dummy_password');
        $foundUser = null;

        foreach ($identifiers as $column) {
            $user = static::findBy($column, $identifier);
            $hash = $user ? $user->$passwordColumn : $dummyHash;

            // Always verify to keep constant-time execution, even if user is not found
            if ($verifier($password, $hash) && $user) {
                $foundUser = $user;
            }
        }

        return $foundUser;
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
        // Retrieve the hash callable and apply it to the current password property
        $hash = static::getAuthFinderConfig()['hash'];
        $password = static::getAuthFinderConfig()['password'];
        $model->$password = $hash($model->$password);
    }
}

