<?php

namespace Potager\Limpid\Definitions;

/**
 * Represents a relationship between models in Limpid.
 *
 * Supported relation types include:
 * - "hasOne"
 * - "hasMany"
 * - "belongsTo"
 * - "manyToMany"
 * - "manyToManyThrough"
 *
 * Fields may be null depending on the relation type.
 *
 * @property string $type              The type of the relation (hasOne, hasMany, belongsTo, manyToMany, manyToManyThrough).
 * @property string $property          The name of the property on the model that defines the relation.
 * @property string $target            The fully-qualified class name of the related model.
 * @property string|null $foreignKey   The foreign key used in hasOne/hasMany/belongsTo relations.
 * @property string|null $localKey     The local key on the current model (used in hasOne/hasMany).
 * @property string|null $ownerKey     The key on the target model used in belongsTo relations.
 * @property string|null $pivotTable   The name of the pivot table (for manyToMany and manyToManyThrough).
 * @property string|null $pivotForeignKey The foreign key column on the pivot table that references the current model.
 * @property string|null $pivotRelatedKey The related key column on the pivot table that references the target model.
 * @property string|null $throughModel The fully-qualified class name of the intermediate pivot model (for manyToManyThrough).
 */
class RelationDefinition
{
    public function __construct(
        public string $type,
        public string $property,
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
}
