<?php

namespace Potager\Limpid\Attributes;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Computed
{
    public ?string $resolver;

    public function __construct(?string $resolver = null)
    {
        $this->resolver = $resolver;
    }
}