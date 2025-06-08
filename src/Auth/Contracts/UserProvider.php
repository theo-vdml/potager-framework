<?php

namespace Potager\Auth\Contracts;

interface UserProvider
{

    /**
     * Get the unique key for the user.
     *
     * @param mixed $user
     * @return mixed
     */
    public function getKey($user);

    /**
     * Retrieve the user by their unique key.
     *
     * @param mixed $key
     * @return mixed
     */
    public function getOriginal($key);

}