<?php

namespace Potager\Limpid\Contracts;

/**
 * Interface AuthStrategy
 *
 * Defines the contract for password hashing and verification strategies
 * used in authentication logic. Implementing classes must provide
 * secure methods to hash plaintext passwords and verify them against stored hashes.
 *
 * This interface allows interchangeable strategies such as Bcrypt, Argon2,
 * or custom hashing mechanisms, providing flexibility and security for user authentication.
 */
interface AuthStrategy
{
    /**
     * Hashes a plaintext password string using a secure algorithm.
     *
     * Implementations should ensure the hash is cryptographically secure,
     * salted (if applicable), and safe to store in a database.
     *
     * @param string $plain The plaintext password to be hashed.
     * @return string A secure, hashed version of the password.
     */
    public function hash(string $plain): string;

    /**
     * Verifies that a plaintext password matches a given hashed password.
     *
     * This method should perform the comparison using a constant-time
     * algorithm to prevent timing attacks.
     *
     * @param string $plain The plaintext password provided by the user.
     * @param string $hash The stored hash to verify against.
     * @return bool True if the password matches the hash, false otherwise.
     */
    public function verify(string $plain, string $hash): bool;
}
