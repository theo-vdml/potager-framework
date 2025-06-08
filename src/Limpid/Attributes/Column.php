<?php

namespace Potager\Limpid\Attributes;
use Attribute;
use Potager\Limpid\Contracts\DataTransformer;
use Potager\Limpid\Contracts\LimpidAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column implements LimpidAttribute
{
    public function __construct(
        public ?string $name = null,
        protected bool $primary = false,
        public ?string $prepare = null,
        public ?string $consume = null,
        public ?DataTransformer $transformer = null
    ) {
    }

    public function isPrimary(): bool
    {
        return $this->primary;
    }
}