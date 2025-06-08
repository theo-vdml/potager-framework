<?php

namespace Potager\Limpid\Strategies;

use Potager\Limpid\Contracts\AuthStrategy;

/**
 * Argon2idStrategy
 *
 * Uses Argon2id algorithm, recommended for most secure applications.
 */
class Argon2idStrategy implements AuthStrategy
{
    public function hash(string $plain): string
    {
        return password_hash($plain, PASSWORD_ARGON2ID);
    }

    public function verify(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }
}
