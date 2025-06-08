<?php

namespace Potager\Auth\Contracts;

interface AuthGuard
{
    public function login($user): void;

    public function logout(): void;

    public function user();
}