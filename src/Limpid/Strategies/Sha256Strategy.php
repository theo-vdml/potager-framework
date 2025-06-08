<?php

namespace Potager\Limpid\Strategies;

use Potager\Limpid\Contracts\AuthStrategy;

/**
 * Sha256Strategy
 *
 * Demonstrates SHA-256 hashing for educational or legacy use.
 * ⚠️ Not recommended for storing passwords in production.
 */
class Sha256Strategy implements AuthStrategy
{
    public function hash(string $plain): string
    {
        return hash('sha256', $plain);
    }

    public function verify(string $plain, string $hash): bool
    {
        return hash_equals($hash, hash('sha256', $plain));
    }
}
