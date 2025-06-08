<?php

namespace Potager\Auth\Providers;

use Potager\Auth\Contracts\UserProvider;
use Potager\Limpid\Model;

class LimpidUserProvider implements UserProvider
{
    public function __construct(private string $model)
    {
    }

    /**
     * Get the unique key for the user.
     *
     * @param mixed $user
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getKey($user)
    {
        if ($user instanceof Model && $user->isPersisted() && $user->getPrimaryKeyValue() !== null) {
            return $user->getPrimaryKeyValue();
        }

        throw new \InvalidArgumentException('The user must be an instance of a persisted' . Model::class);
    }

    /**
     * Retrieve the user by their unique key.
     *
     * @param mixed $key
     * @return Model|null
     */
    public function getOriginal($key): ?Model
    {
        return $this->model::find($key);
    }
}
