<?php

namespace Potager\Limpid\Attributes;
use Attribute;
use Potager\Limpid\Contracts\LimpidAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Computed implements LimpidAttribute
{
    public function __construct(
        protected ?string $resolver = null
    ) {
    }

    public function getResolver(): string|null
    {
        return $this->resolver;
    }
}