<?php

namespace Potager\Limpid\Strategies;

use Potager\Limpid\Contracts\AuthStrategy;

/**
 * PlainTextStrategy
 *
 * Stores plaintext passwords. ⚠️ Never use in production.
 */
class PlainTextStrategy implements AuthStrategy
{
    public function hash(string $plain): string
    {
        return $plain;
    }

    public function verify(string $plain, string $hash): bool
    {
        return hash_equals($plain, $hash);
    }
}
