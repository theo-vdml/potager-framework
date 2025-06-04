<?php

namespace Potager\Limpid\Boot;

use Potager\Limpid\Attributes\Table;
use Potager\Limpid\Definitions\ColumnDefinition;
use Potager\Limpid\Definitions\ComputedDefinition;
use Potager\Limpid\Definitions\ModelDefinition;
use Potager\Limpid\Exceptions\DuplicateColumnException;
use Potager\Limpid\Exceptions\InvalidAttributeCombinationException;
use Potager\Limpid\Exceptions\InvalidBootMethodException;
use Potager\Limpid\Exceptions\InvalidComputedResolverVisibilityException;
use Potager\Limpid\Exceptions\MissingComputedResolverException;
use Potager\Limpid\Attributes\Column;
use Potager\Limpid\Attributes\Computed;
use Potager\Limpid\Attributes\Hook;
use Potager\Limpid\Exceptions\MultiplePrimaryKeyException;
use Potager\Limpid\Model;
use Potager\Support\Str;
use Potager\Support\Utils;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;


/**
 * Responsible for bootstrapping Limpid model metadata,
 * parsing properties, methods, and traits to generate
 * a ModelMeta structure, register hooks, and invoke boot methods.
 */
class ModelBooter
{
    /**
     * Boot a given model class by analyzing its metadata,
     * registering computed properties and hooks, and invoking
     * boot methods for traits and the model itself.
     *
     * @param string $modelClass Fully-qualified model class name
     * @return ModelDefinition The booted model's metadata
     * @throws MissingComputedResolverException If a Computed property has no resolver method
     */
    public static function boot(string $modelClass): ModelDefinition
    {
        $reflection = new ReflectionClass($modelClass);
        $meta = new ModelDefinition();

        /**
         * ─────────────────────────────────────────────────────────────
         * TABLE NAME RESOLUTION
         * ─────────────────────────────────────────────────────────────
         * Determine the database table name for the model.
         *
         * Resolution order:
         *   1. #[Table("custom_name")] attribute on the class
         *   2. static tableName(): string method on the model
         *   3. Defaults to snake_case + pluralized class base name
         *      e.g., PostCategory → post_categories
         */
        $table = null;
        $tableAttr = $reflection->getAttributes(Table::class)[0] ?? null;
        if ($tableAttr) {
            $table = $tableAttr->newInstance()->name;
        } else if ($reflection->hasMethod('tableName')) {
            $method = $reflection->getMethod('tableName');
            $returnType = $method->getReturnType();
            $isStringReturn = $returnType && $returnType->getName() === 'string';
            if ($method->isStatic() && $isStringReturn) {
                $table = $method->invoke(null);
            } else {
                // TODO : Add a WARNING log
                // Message: Model {model name} defines 'tableName' but it must be a static method with return type of string. Ignored.
            }
        }
        $meta->table = $table ?? Str::pluralize(Str::toSnakeCase(Utils::classBasename($modelClass)));

        /**
         * ─────────────────────────────────────────────────────────────
         * PROPERTY PARSING: COLUMNS & COMPUTEDS
         * ─────────────────────────────────────────────────────────────
         * Loop through all class properties to extract metadata.
         *
         * - #[Column]: Registered in $meta->columns as ColumnMeta
         * - #[Computed]: Registered in $meta->computeds as ComputedMeta
         *   - Resolver method is required (explicit or inferred)
         *   - Missing resolvers will throw MissingComputedResolverException
         */

        $primaryKeyAssigned = false;

        /** @var ReflectionProperty $property */
        foreach ($reflection->getProperties() as $property) {
            $propertyName = $property->getName();
            $propertyType = $property->getType()?->getName() ?? null;
            $attrs = self::extractAttributes($property);

            // Prevent using both Column AND Computed on the same property
            if (isset($attrs[Column::class]) && isset($attrs[Computed::class])) {
                throw new InvalidAttributeCombinationException($modelClass, $propertyName);
            }

            // Handle #[Column] attribute
            if (isset($attrs[Column::class])) {
                /** @var Column $column */
                $column = $attrs[Column::class]();
                if (isset($meta->columns[$propertyName])) {
                    throw new DuplicateColumnException($modelClass, $propertyName);
                }
                if ($column->isPrimary()) {
                    if ($primaryKeyAssigned) {
                        throw new MultiplePrimaryKeyException($modelClass, $meta->primary, $propertyName);
                    }
                    $primaryKeyAssigned = true;
                    $meta->primary = $propertyName;
                }
                $meta->columns[$propertyName] = new ColumnDefinition(
                    name: $propertyName,
                    type: $propertyType,
                    isPrimary: $column->isPrimary()
                );
            }
            // Handle #[Computed] attribute
            else if (isset($attrs[Computed::class])) {
                /** @var Computed $computed */
                $computed = $attrs[Computed::class]();
                $resolver = $computed->getResolver() ?? Str::toCamelCase("compute {$propertyName}");
                // Ensure Resolver Exists
                if (!$reflection->hasMethod($resolver))
                    throw new MissingComputedResolverException($modelClass, $propertyName, $resolver);
                $resolverMethod = $reflection->getMethod($resolver);
                // Ensure Resolver is public
                if (!$resolverMethod->isPublic()) {
                    throw new InvalidComputedResolverVisibilityException($modelClass, $propertyName, $resolver);
                }
                $meta->computeds[$propertyName] = new ComputedDefinition(
                    property: $propertyName,
                    resolver: $resolver
                );
            }
        }

        /**
         * ─────────────────────────────────────────────────────────────
         * METHOD PARSING: HOOKS
         * ─────────────────────────────────────────────────────────────
         * Loop through all class methods and extract #[Hook] attributes.
         *
         * For each #[Hook], register the associated callback to the model’s
         * lifecycle event via Model::registerHookOn().
         * 
         * Callback is automatically bound to the model instance (unless static).
         */
        /** @var ReflectionMethod $method */
        foreach ($reflection->getMethods() as $method) {
            $attrs = self::extractAttributes($method);

            // Handle #[Hook] attribute
            if (isset($attrs[Hook::class])) {
                /** @var Hook $hook */
                $hook = $attrs[Hook::class]();
                foreach ($hook->getEvents() as $event) {
                    $callback = function ($model) use ($method) {
                        $method->setAccessible(true);
                        $method->invoke($method->isStatic() ? null : $model, $model);
                    };
                    Model::registerHookOn($modelClass, $event, $callback, $hook->getPriority());
                }
            }
        }

        /**
         * ─────────────────────────────────────────────────────────────
         * TRAIT BOOTING
         * ─────────────────────────────────────────────────────────────
         * If the model uses traits and defines boot methods for them,
         * automatically call them using the naming convention:
         *   boot{TraitName}()
         *
         * e.g., bootSoftDeletes() for use SoftDeletes;
         */
        foreach ($reflection->getTraits() as $trait) {
            /** @var ReflectionClass $trait */
            $traitName = $trait->getName();
            $traitBaseName = Utils::classBasename($traitName);
            $traitBootMethod = Str::toCamelCase("boot {$traitBaseName}");
            if ($reflection->hasMethod($traitBootMethod)) {
                $method = $reflection->getMethod($traitBootMethod);
                $hasRequiredParameters = array_filter($method->getParameters(), fn($param) => !$param->isOptional());
                if (!$method->isStatic() || !$method->isPublic() || count($hasRequiredParameters) > 0) {
                    throw new InvalidBootMethodException($modelClass, $traitBootMethod);
                }
                $method->invoke(null);
            }
        }


        /**
         * ─────────────────────────────────────────────────────────────
         * MODEL BOOT METHOD
         * ─────────────────────────────────────────────────────────────
         * Check if the model defines a boot method for itself using:
         *   boot{ClassName}()
         *
         * If so, invoke it (assumed static).
         */
        $classBaseName = Utils::classBasename($modelClass);
        $bootMethod = Str::toCamelCase("boot {$classBaseName}");
        if ($reflection->hasMethod($bootMethod)) {
            $method = $reflection->getMethod($traitBootMethod);
            $hasRequiredParameters = array_filter($method->getParameters(), fn($param) => !$param->isOptional());
            if (!$method->isStatic() || !$method->isPublic() || count($hasRequiredParameters) > 0) {
                throw new InvalidBootMethodException($modelClass, $bootMethod);
            }
            $method->invoke(null);
        }

        /**
         * ─────────────────────────────────────────────────────────────
         * DONE
         * ─────────────────────────────────────────────────────────────
         * Return the fully constructed metadata structure.
         */
        return $meta;
    }

    /**
     * Extract and defer instantiation of attributes from a property or method.
     * Uses lazy-loading via closures to minimize overhead.
     *
     * @param ReflectionProperty|ReflectionMethod $reflector
     * @return array<class-string, Closure(): object> Lazily-instantiable attribute map
     */
    private static function extractAttributes(ReflectionProperty|ReflectionMethod $reflector): array
    {
        $attrs = [];
        foreach ($reflector->getAttributes() as $attr) {
            $attrs[$attr->getName()] = fn(): object => $attr->newInstance();
        }
        return $attrs;
    }
}