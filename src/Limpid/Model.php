<?php

namespace Potager\Limpid;

use InvalidArgumentException;
use Potager\App;
use Potager\Limpid\Boot\ModelBooter;
use Potager\Limpid\Definitions\ColumnDefinition;
use Potager\Limpid\Definitions\ComputedDefinition;
use Potager\Limpid\Definitions\ModelDefinition;
use Pixie\QueryBuilder\QueryBuilderHandler;
use Potager\Support\Str;
use Potager\Support\Utils;
use stdClass;
use DateTime;

/**
 * Base abstract model class for ORM functionality.
 * Handles model definition loading, attributes, and lifecycle tracking.
 */
abstract class Model
{
    /* 
     * -----------------------------------------------------------------------------------------------------------------
     *                                 PROPERTY DECLARATIONS & STATIC CACHES
     * -----------------------------------------------------------------------------------------------------------------
     *
     * These properties include all instance attributes, state flags, and static caches used
     * to store model definitions and event hooks. They serve as the backbone to track the
     * model's internal state and metadata, and optimize lookups across instances.
     *
     * - $_definitions: caches model definitions per class for efficient reuse.
     * - $_hooks: stores event hooks by class and event.
     * - $_persisted: tracks if instance is saved in DB.
     * - $_originals: snapshot of original attribute values.
     * - $_attributes: current attribute values corresponding to columns.
     * - $_extras: any additional properties set on the model outside of columns.
     */

    /**
     * Cached model definitions for each model class.
     * 
     * @var array<string, ModelDefinition>
     */
    protected static array $_definitions = [];

    /**
     * Registered hooks for model events, grouped by class and event name.
     * 
     * @var array<string, array<string, array<int, array{callback: callable, priority: int}>>>
     */
    protected static array $_hooks = [];

    /**
     * Indicates whether the model instance exists in the database.
     * 
     * @var bool
     */
    protected bool $_persisted = false;

    /**
     * The original attribute values when the model was loaded or saved.
     * 
     * @var array<string, mixed>
     */
    protected array $_originals = [];

    /**
     * The current attribute values of the model.
     * 
     * @var array<string, mixed>
     */
    protected array $_attributes = [];

    /**
     * The extra values sets to the Model but not defined
     * 
     * @var array<string, mixed>
     */
    protected array $_extras = [];

    /*
     * -----------------------------------------------------------------------------------------------------------------
     *                                 MODEL DEFINITION & METADATA HELPERS (INTERNAL API)
     * -----------------------------------------------------------------------------------------------------------------
     *
     * These static methods provide access to the model’s schema definitions and metadata, such as columns and computed properties.
     * They are intended primarily for internal ORM usage to retrieve and cache model structure information,
     * allowing the framework to validate and handle properties dynamically.
     * 
     * _hasColumn(), _getColumn(), _hasComputed(), _getComputed(), getDefinition(), getDatabase()
     */

    /**
     * Check if the model has a column with the given name.
     *
     * @internal
     */
    public static function _hasColumn(string $name): bool
    {
        return static::getDefinition()->hasColumn($name);
    }

    /**
     * Get the column definition for a given name.
     *
     * @internal
     */
    public static function _getColumn(string $name): ColumnDefinition|null
    {
        return static::getDefinition()->getColumn($name);
    }

    /**
     * Check if the model has a computed property with the given name.
     *
     * @internal
     */
    public static function _hasComputed(string $name): bool
    {
        return static::getDefinition()->hasComputed($name);
    }

    /**
     * Get the computed property definition for a given name.
     *
     * @internal
     */
    public static function _getComputed(string $name): ComputedDefinition|null
    {
        return static::getDefinition()->getComputed($name);
    }

    /**
     * Returns the model definition for the current model class,
     * bootstrapping it if it's not already cached.
     *
     * @return ModelDefinition
     */
    public static function getDefinition(): ModelDefinition
    {
        $class = static::class;
        if (!isset(static::$_definitions[$class]))
            static::$_definitions[$class] = ModelBooter::boot($class);
        return static::$_definitions[$class];
    }

    /**
     * Returns the application's database instance.
     *
     * @return Database The database connection instance.
     */
    protected static function getDatabase(): Database
    {
        return App::getInstance()->getDatabase();
    }



    /*
     * -----------------------------------------------------------------------------------------------------------------
     *                                 CONSTRUCTION & ATTRIBUTE INITIALIZATION
     * -----------------------------------------------------------------------------------------------------------------
     *
     * Methods related to creating and initializing model instances live here.
     * This includes constructors, bootstrapping routines to reset internal state,
     * hydration from storage or raw data, and merging/filling attributes.
     * These methods ensure the model instance is prepared with proper attribute values,
     * and manage tracking of original vs. modified states.
     * 
     * __construct(), boot(), hydrateOriginals(), fill(), merge(), hydrateFromStorage(), newUnsavedInstance(), factory()
     */

    /**
     * Constructs a new model instance
     */
    public function __construct()
    {
        $this->boot();
    }

    /**
     * Initializes the model by unsetting all column and computed property names
     * from the definition to ensure a clean slate.
     *
     * This prevents magic properties from conflicting with internal tracking logic.
     *
     * @return void
     */
    protected function boot(): void
    {
        $definition = static::getDefinition();
        foreach (array_keys($definition->columns) as $column)
            unset($this->$column);
        foreach (array_keys($definition->computeds) as $computed)
            unset($this->$computed);
    }

    /**
     * Sync originals with the attributes. After this `isDirty` will
     * return false
     */
    private function hydrateOriginals(): void
    {
        $this->_originals = [];
        $this->_originals = array_merge($this->_originals, $this->_attributes);
    }

    /**
     * Set bulk attributes on the model instance.
     * 
     * @param \stdClass|array $values
     * @param bool $allowExtraProperties
     * @return Model
     */
    protected function fill(stdClass|array $values, bool $allowExtraProperties): static
    {
        $this->_attributes = [];
        $this->merge($values, $allowExtraProperties);
        return $this;
    }

    /**
     * Merge values into the model properties.
     *
     * @param stdClass|array $values Input values to merge (array or stdClass).
     * @param bool $allowExtraProperties Whether to allow properties not defined in the model.
     * @return static Returns the current model instance for method chaining.
     * @throws InvalidArgumentException Throws if a property is undefined and extras are disallowed.
     * @internal Internal method, not intended for public API use.
     */
    public function merge(stdClass|array $values, bool $allowExtraProperties = false): static
    {
        /**
         * Get the short class name for error messages or logging.
         */
        $model = Utils::classBasename(static::class);

        /**
         * Convert stdClass object to array for easier iteration.
         */
        if ($values instanceof stdClass) {
            $values = (array) $values;
        }

        /**
         * Iterate through each key-value pair to merge into the model.
         */
        foreach ($values as $key => $value) {

            /**
             * If the key corresponds to a defined database column,
             * assign the value to the internal attributes array.
             */
            if (static::_hasColumn($key)) {
                $this->{$key} = $value;
                continue;
            }

            /**
             * If the key corresponds to a computed property,
             * ignore it since it's derived, not set directly.
             */
            if (static::_hasComputed($key)) {
                continue;
            }

            /**
             * If the property physically exists on the object,
             * assign the value directly to the property.
             */
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
                continue;
            }

            /**
             * If extra properties are not allowed and the key
             * doesn't match any known property, throw an exception.
             */
            if (!$allowExtraProperties) {
                throw new InvalidArgumentException("Cannot define \"{$key}\" on \"{$model}\" model, since it is not defined as a model property ");
            }

            /**
             * Otherwise, store the extra property in the _extras array.
             */
            $this->_extras[$key] = $value;
        }

        /**
         * Return the current instance to support method chaining.
         */
        return $this;
    }

    /**
     * Instantiates a model from data retrieved from persistent storage (e.g. a database).
     *
     * This method:
     * - Fills the model with the provided data.
     * - Marks the instance as persisted (i.e. it exists in storage).
     * - Syncs original attribute values to prevent dirty tracking.
     *
     * @param stdClass|array $values The data from storage used to populate the model.
     * @return static A fully hydrated, non-dirty model marked as persisted.
     */
    protected static function hydrateFromStorage(stdClass|array $values): static
    {
        $instance = new static();
        $instance->_persisted = true;
        $instance->fill($values, true);
        $instance->hydrateOriginals();
        return $instance;
    }

    /**
     * Creates and returns a new model instance representing a fresh, unsaved record.
     *
     * This method fills the model with provided attributes, disallows any extra
     * (undefined) properties, and leaves the instance marked as non-persisted.
     *
     * @param stdClass|array $value The attribute values to populate the model with.
     * @return static A new model instance not yet persisted to the database.
     */
    protected static function newUnsavedInstance(stdClass|array $value): static
    {
        $instance = new static();
        $instance->fill($value, false);
        return $instance;
    }

    /**
     * Creates a new model instance using the given attributes.
     *
     * This is a convenience method equivalent to calling `newUnsavedInstance()`,
     * intended for test factories or dynamic instantiation.
     *
     * @param array $attributes The initial attributes for the model.
     * @return static A new unsaved model instance.
     */
    public static function factory(array $attributes = []): static
    {
        return static::newUnsavedInstance($attributes);
    }

    /*
     * -----------------------------------------------------------------------------------------------------------------
     *                                 EVENT HOOKS MANAGEMENT
     * -----------------------------------------------------------------------------------------------------------------
     *
     * This section handles the registration and triggering of event hooks or callbacks.
     * Hooks allow injection of custom logic around model lifecycle events, such as before/after saving or deleting.
     * Hooks are prioritized and executed in order, providing flexible extensibility for models.
     * 
     * registerHookOn(), registerHook(), triggerModelHook()
     */

    /**
     * Registers a hook callback on a specific model class for a given event.
     * Hooks are stored per class and sorted by priority (descending).
     *
     * @internal This method is intended for internal use by the ORM framework.
     *
     * @param string   $class     The fully qualified class name to register the hook on.
     * @param string   $event     The event name (e.g. 'beforeSave', 'afterDelete').
     * @param callable $callback  A callback to execute when the event is triggered.
     * @param int      $priority  Determines execution order (higher = earlier).
     * @return void
     */
    public static function registerHookOn(string $class, string $event, callable $callback, int $priority = 0): void
    {
        static::$_hooks[$class][$event][] = [
            "callback" => $callback,
            "priority" => $priority
        ];
        usort(static::$_hooks[$class][$event], fn($a, $b): int => $b['priority'] <=> $a['priority']);
    }

    /**
     * Registers a hook callback on the current model class for a given event.
     * This is a shorthand for `registerHookOn()` using the static model class.
     *
     * @param string   $event     The event name (e.g. 'beforeSave', 'onCreate').
     * @param callable $callback  A callback to execute when the event is triggered.
     * @param int      $priority  Execution priority (higher runs earlier).
     * @return void
     */
    public static function registerHook(string $event, callable $callback, int $priority = 0): void
    {
        static::registerHookOn(static::class, $event, $callback, $priority);
    }

    /**
     * Triggers all registered hook callbacks for the specified event on this model instance.
     *
     * Hooks are executed in order of descending priority (highest first).
     * Each hook callback receives this model instance as its sole argument.
     *
     * @param string $event The event name to trigger (e.g. 'beforeSave', 'afterCreate').
     * @return void
     */
    protected function triggerModelHook(string $event): void
    {
        $hooks = static::$_hooks[static::class][$event] ?? [];

        foreach ($hooks as $hook) {
            $callback = $hook['callback'];
            $callback($this);
        }
    }

    /*
     * -----------------------------------------------------------------------------------------------------------------
     *                                 PERSISTENCE OPERATIONS (CREATE, READ, UPDATE, DELETE)
     * -----------------------------------------------------------------------------------------------------------------
     *
     * Contains all methods responsible for database interaction and persistence management.
     * This includes static methods to find, create, and retrieve models, instance methods to save and delete,
     * and query builder accessors. It’s the core CRUD interface that connects the model with the data layer.
     */

    public function isPersisted()
    {
        return $this->_persisted;
    }

    public static function create(array $data): static
    {
        $instance = static::newUnsavedInstance($data);
        $instance->save();
        return $instance;
    }

    public static function createMany()
    {

    }

    public static function find(int $id): static|null
    {
        $data = static::query()->where('id', $id)->first();
        return $data ? static::hydrateFromStorage($data) : null;
    }

    public static function findBy(string $column, string $value)
    {
        $data = static::query()->where($column, $value)->first();
        return $data ? static::hydrateFromStorage($data) : null;
    }

    public static function findOrFail()
    {

    }

    public static function findByOrFail()
    {

    }

    public static function findMany()
    {

    }

    public static function findManyOrFail()
    {

    }

    public function save()
    {
        $this->triggerModelHook('beforeSave');

        $columns = static::getDefinition()->getColumns();
        $data = [];

        foreach ($columns as $propertyName => $definition) {
            $column = Str::toSnakeCase($propertyName);
            $value = $this->$propertyName ?? null;

            if ($value instanceof DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }

            $data[$column] = $value;
        }

        $builder = static::query();

        if (!$this->isPersisted()) {
            $id = $builder->insert($data);

            $primaryKey = static::$modelMetas[static::class]['primary'] ?? 'id'; // ! NON EXISTING PROPS, OUTDATED SINCE REFACTOR

            if ($primaryKey && !isset($this->$primaryKey)) {
                $this->$primaryKey = $id;
            }
            $this->_persisted = true;
            $this->triggerModelHook('onCreate');
        } else {
            // Update (a faire)
        }

        $this->triggerModelHook('afterSave');

    }

    public function saveOrFail()
    {

    }

    public function delete()
    {

    }

    public function deleteOrFail()
    {

    }

    /**
     * Get a new query builder instance for the model's table.
     *
     * @return QueryBuilderHandler A query builder instance scoped to the model's table.
     */
    public static function query(): QueryBuilderHandler
    {
        return Database::table(static::getDefinition()->getTable());
    }

    /*
     * -----------------------------------------------------------------------------------------------------------------
     *                                 MAGIC PROPERTY ACCESSORS
     * -----------------------------------------------------------------------------------------------------------------
     *
     * Defines dynamic property access via __get and __set magic methods.
     * These allow transparent reading and writing of model attributes, computed properties,
     * and extras, enabling flexible and concise syntax for model interaction.
     * 
     * __get(), __set()
     */

    /**
     * Magic getter to access model properties dynamically.
     *
     * @param string $key The property name being accessed.
     * @return mixed|null Returns computed value, column value, or extra property, or null if not found.
     */
    public function __get($key)
    {
        /**
         * If the key corresponds to a computed property,
         * resolve and return the computed value via its resolver method.
         */
        if (static::_hasComputed($key)) {
            $resolver = static::_getComputed($key)->resolver;
            return $this->$resolver();
        }

        /**
         * If the key corresponds to a defined column,
         * return the stored attribute value or null if not set.
         */
        if (static::_hasColumn($key)) {
            return $this->_attributes[$key] ?? null;
        }

        /**
         * Otherwise, return the value from extras if present, or null.
         */
        return $this->_extras[$key] ?? null;
    }


    /**
     * Magic setter to assign values to model properties dynamically.
     *
     * @param string $key The property name being set.
     * @param mixed $value The value to assign.
     * @return void
     */
    public function __set($key, $value)
    {
        if (static::_hasColumn($key)) {
            $this->_attributes[$key] = $value;
        }
    }

}