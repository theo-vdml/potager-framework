<?php

namespace Potager\Limpid\Definitions;

use Potager\Limpid\Contracts\DataTransformer;


/**
 * Represents the definition of a database column.
 */
class ColumnDefinition
{



    /**
     * @param string      $name       The name of the column in database (snake_case).
     * @param string      $property   The name of the model property it rely to.
     * @param string|null $type       The SQL type of the column (e.g., 'string', 'int', etc.), or null if unspecified.
     * @param bool        $isPrimary  Whether this column is a primary key.
     * @param string|null $prepare The name of the method used to prepare the data before storage.
     * @param string|null $consume The name of the method used to consume the data after retrieval.
     * @param DataTransformer|null $transformer An optional transformer for handling both preparation and consumption of data.
     */
    public function __construct(
        public string $name,
        public string $property,
        public ?string $type = null,
        public bool $isPrimary = false,
        public ?string $prepare = null,
        public ?string $consume = null,
        public ?DataTransformer $transformer = null
    ) {
    }
}
