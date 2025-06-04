<?php

namespace Potager\Limpid\Attributes;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Computed
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