<?php

namespace Potager\Limpid\Strategies;

use Potager\Limpid\Contracts\AuthStrategy;

/**
 * CustomPepperedBcryptStrategy
 *
 * Bcrypt hashing with an additional application-level pepper.
 */
class CustomPepperedBcryptStrategy implements AuthStrategy
{
    private string $pepper;

    public function __construct(string $pepper)
    {
        $this->pepper = $pepper;
    }

    public function hash(string $plain): string
    {
        return password_hash($plain . $this->pepper, PASSWORD_BCRYPT);
    }

    public function verify(string $plain, string $hash): bool
    {
        return password_verify($plain . $this->pepper, $hash);
    }
}
