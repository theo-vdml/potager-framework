<?php

namespace Potager\Limpid\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Auth
{

    public function __construct(protected array|string|null $identifiers = null, protected string|null $password = null, protected string|array|null $strategy = null)
    {
        if (is_string($this->identifiers))
            $this->identifiers = [$this->identifiers];
    }

    public function getConfig(): array
    {
        $config = [];
        if (isset($this->identifiers))
            $config['identifiers'] = $this->identifiers;
        if (isset($this->password))
            $config['password'] = $this->password;
        if (isset($this->strategy))
            $config['strategy'] = $this->strategy;
        return $config;
    }

}