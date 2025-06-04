<?php

namespace Potager\Limpid\Definitions;

/**
 * Represents the definition of a database column.
 */
class ColumnDefinition
{
    /**
     * @param string      $name       The name of the column.
     * @param string|null $type       The SQL type of the column (e.g., 'string', 'int', etc.), or null if unspecified.
     * @param bool        $isPrimary  Whether this column is a primary key.
     */
    public function __construct(
        public string $name,
        public ?string $type = null,
        public bool $isPrimary = false,
    ) {
    }
}
