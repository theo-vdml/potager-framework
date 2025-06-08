<?php

namespace Potager\Limpid\Attributes;

use Potager\Limpid\Contracts\LimpidAttribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Table implements LimpidAttribute
{
    public function __construct(public string $name)
    {
    }
}