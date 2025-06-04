<?php

namespace Potager\Limpid\Attributes;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    public function __construct(
        protected bool $isPrimary = false
    ) {
    }

    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }
}