<?php

namespace Potager\Limpid\Strategies;

use Potager\Limpid\Contracts\AuthStrategy;

/**
 * Argon2iStrategy
 *
 * Uses Argon2i algorithm for secure password hashing.
 */
class Argon2iStrategy implements AuthStrategy
{
    public function hash(string $plain): string
    {
        return password_hash($plain, PASSWORD_ARGON2I);
    }

    public function verify(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }
}
