<?php

namespace Potager\Limpid\Attributes;

use Attribute;
use Potager\Limpid\Contracts\LimpidAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
abstract class Relation implements LimpidAttribute
{
    public function __construct(
        public string $target,
        public ?string $foreignKey = null,
        public ?string $localKey = null,
        public ?string $ownerKey = null,
        public ?string $pivotTable = null,
        public ?string $pivotForeignKey = null,
        public ?string $pivotRelatedKey = null,
        public ?string $throughModel = null,
    ) {
    }

    abstract public string $type;
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class HasOne extends Relation
{
    public string $type = 'hasOne';
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class HasMany extends Relation
{
    public string $type = 'hasMany';
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class BelongsTo extends Relation
{
    public string $type = 'belongsTo';
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class ManyToMany extends Relation
{
    public string $type = 'manyToMany';
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class HasManyThrough extends Relation
{
    public string $type = 'hasManyThrough';
}
