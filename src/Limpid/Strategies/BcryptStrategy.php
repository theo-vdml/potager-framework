<?php

namespace Potager\Limpid\Strategies;

use Potager\Limpid\Contracts\AuthStrategy;

/**
 * BcryptStrategy
 *
 * Implements password hashing and verification using PHP's bcrypt algorithm.
 */
class BcryptStrategy implements AuthStrategy
{
    /**
     * Hashes a plaintext password using bcrypt.
     *
     * @param string $plain The plaintext password.
     * @return string The bcrypt hash.
     */
    public function hash(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT);
    }

    /**
     * Verifies a plaintext password against a bcrypt hash.
     *
     * @param string $plain The plaintext password.
     * @param string $hash The stored bcrypt hash.
     * @return bool True if valid, false otherwise.
     */
    public function verify(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }
}
