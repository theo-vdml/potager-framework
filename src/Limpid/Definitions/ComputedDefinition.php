<?php

namespace Potager\Limpid\Definitions;

/**
 * Represents a computed property definition for a model.
 */
class ComputedDefinition
{
    /**
     * @param string $property The name of the property to be computed.
     * @param string $resolver The method name or callable that computes the property's value.
     */
    public function __construct(
        public string $property,
        public string $resolver
    ) {
    }
}
