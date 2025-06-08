<?php

namespace Potager\Auth;

use Potager\Auth\Contracts\AuthGuard;

/**
 * Authenticator manages multiple authentication guards and provides methods to interact with them.
 */
class Authenticator
{
    /**
     * Registered authentication guards.
     * @var array<string, AuthGuard>
     */
    protected array $guards = [];

    /**
     * Default guard name.
     * @var string|null
     */
    protected ?string $defaultGuard = null;

    /**
     * The guard to use for the current operation.
     * If not set, the default guard will be used.
     * @var AuthGuard|null
     */
    protected ?AuthGuard $guardToUse = null;

    /**
     * The guard used for authentication.
     * @var AuthGuard|null
     */
    protected ?AuthGuard $authenticatedUsing = null;

    /**
     * The currently authenticated user.
     * This is set after a successful login.
     * @var mixed|null
     */
    protected $authenticatedUser = null;

    /**
     * Get the default guard instance.
     *
     * @return AuthGuard The default guard.
     * @throws \RuntimeException If no default guard is set or if the default guard is not registered.
     */
    protected function getDefaultGuard(): AuthGuard
    {
        if ($this->defaultGuard === null && count($this->guards) === 1) {
            $this->defaultGuard = array_key_first($this->guards);
        }

        if ($this->defaultGuard === null) {
            throw new \RuntimeException('Default guard is not set for Authentication, please set one or specify the guard you want to use.');
        }

        if (!isset($this->guards[$this->defaultGuard])) {
            throw new \RuntimeException("Default guard {$this->defaultGuard} is not a registered guard. Please register it first.");
        }

        return $this->guards[$this->defaultGuard];
    }

    /**
     * Get a specific guard by name.
     *
     * @param string $name The name of the guard to retrieve.
     * @return AuthGuard The requested guard.
     * @throws \RuntimeException If the guard is not registered.
     */
    public function getGuard(string $name): AuthGuard
    {
        if (!isset($this->guards[$name])) {
            throw new \RuntimeException("Guard {$name} is not registered.");
        }

        return $this->guards[$name];
    }

    /**
     * Get the guard to use for the current operation.
     *
     * @return AuthGuard The guard to use.
     */
    public function getGuardToUse(): AuthGuard
    {
        $guard = $this->guardToUse ?? $this->getDefaultGuard();
        $this->guardToUse = null; // Reset after use
        return $guard;
    }

    /**
     * Register a new guard.
     *
     * @param string $name The name of the guard.
     * @param AuthGuard $guard The guard instance.
     * @throws \RuntimeException If the guard is already registered.
     */
    public function registerGuard(string $name, AuthGuard $guard): void
    {
        if (isset($this->guards[$name])) {
            throw new \RuntimeException("Guard {$name} is already registered.");
        }

        $this->guards[$name] = $guard;
    }

    /**
     * Set the default guard.
     *
     * @param string $name The name of the guard to set as default.
     * @throws \RuntimeException If the guard is not registered.
     */
    public function setDefaultGuard(string $name): void
    {
        if (!isset($this->guards[$name])) {
            throw new \RuntimeException("Guard {$name} is not registered.");
        }

        $this->defaultGuard = $name;
    }

    /**
     * Specify which guard to use for the next operation.
     *
     * @param string $name The name of the guard to use.
     * @return self
     * @throws \RuntimeException If the guard is not registered.
     */
    public function use(string $name): self
    {
        if (!isset($this->guards[$name])) {
            throw new \RuntimeException("Guard {$name} is not registered.");
        }

        $this->guardToUse = $this->guards[$name];
        return $this;
    }

    /**
     * Log in a user using the selected guard.
     *
     * @param mixed $user The user to log in.
     */
    public function login($user): void
    {
        $this->getGuardToUse()->login($user);
        $this->authenticatedUser = $user;
    }

    /**
     * Log out the current user using the selected guard.
     */
    public function logout(): void
    {
        $guardToUse = $this->authenticatedUsing ?? $this->getGuardToUse();
        $guardToUse->logout();
        $this->authenticatedUser = null;
        $this->authenticatedUsing = null;
    }

    /**
     * Authenticate the user and set the authenticated user.
     *
     * @return mixed The authenticated user.
     * @throws \RuntimeException If no user is authenticated.
     */
    public function authenticate(): mixed
    {
        $user = $this->authenticateQuietly();
        if ($user === null) {
            throw new \RuntimeException('No user is authenticated.');
        }
        return $this->authenticatedUser;
    }

    /**
     * Attempt to authenticate the user quietly without throwing exceptions.
     *
     * @return mixed|null The authenticated user or null if authentication fails.
     */
    public function authenticateQuietly(): mixed
    {
        if ($this->authenticatedUser !== null) {
            return $this->authenticatedUser; // Already authenticated
        }
        $guard = $this->getGuardToUse();
        $user = $guard->user();
        if ($user === null) {
            return null;
        }
        $this->authenticatedUser = $user;
        $this->authenticatedUsing = $guard;
        return $this->authenticatedUser;
    }

    /**
     * Get the authenticated user.
     *
     * @return mixed|null The authenticated user or null if not authenticated.
     */
    public function user(): mixed
    {
        return $this->authenticatedUser;
    }

    /**
     * Get the authenticated user or fail with an exception if not authenticated.
     *
     * @return mixed The authenticated user.
     * @throws \RuntimeException If not authenticated.
     */
    public function getUserOrFail(): mixed
    {
        if ($this->authenticatedUser === null) {
            throw new \RuntimeException('No user is authenticated.');
        }
        return $this->authenticatedUser;
    }

    /**
     * Check if a user is authenticated.
     *
     * @return bool True if a user is authenticated, false otherwise.
     */
    public function isAuthenticated(): bool
    {
        return $this->authenticatedUser !== null;
    }
}
