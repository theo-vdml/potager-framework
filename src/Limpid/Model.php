<?php

namespace Potager\Limpid;

use Potager\App;
use Potager\Limpid\Attributes\Column;
use Potager\Limpid\Attributes\Hook;
use Potager\Limpid\Attributes\Primary;
use Potager\Limpid\Attributes\Computed;
use Potager\Limpid\Exceptions\MissingComputedResolverException;
use Potager\Support\Str;
use Potager\Support\Utils;
use DateTime;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

abstract class Model
{
    protected static array $modelMetas = [];
    protected static ?string $tableName = null;
    protected static array $hooks = [];

    protected bool $presisted = false;


    /*

                BOOTSTRAP
        (parse the Model to do the magic)
    
    */

    /**
     * Static method to parse the Model and store all its metas
     */
    protected static function bootModel(): void
    {
        // Get the model name
        $class = static::class;

        // Prevent double boot
        if (isset(static::$modelMetas[$class]))
            return;

        // Reflect the class to access it's properties and methods
        $reflection = new ReflectionClass($class);

        // Set empty arrays for the model metas
        static::$modelMetas[$class] = [];
        static::$modelMetas[$class]['columns'] = [];
        static::$modelMetas[$class]['primary'] = [];
        static::$modelMetas[$class]['computed'] = [];

        foreach ($reflection->getProperties() as $property) {
            if (!($property instanceof ReflectionProperty))
                continue;

            // Collect infos about property
            $propertyName = $property->getName();
            $propertyType = $property->getType();

            // Collect all attributes for this property
            $attributes = [];
            foreach ($property->getAttributes() as $attr) {
                $attrName = $attr->getName();
                $attributes[$attrName] = $attr->newInstance();
            }

            // Check if the property is a column
            static::processColumn($class, $propertyName, $propertyType, $property, $attributes);
            // Check if the property is a computed property
            static::processComputed($class, $propertyName, $attributes, $reflection);
        }

        foreach ($reflection->getMethods() as $method) {
            if (!($method instanceof ReflectionMethod))
                continue;

            foreach ($method->getAttributes(Hook::class) as $hook) {
                /** @var Hook $instance */
                $instance = $hook->newInstance();

                foreach ($instance->events as $event) {
                    static::registerHook($event, function ($model) use ($method) {
                        $method->setAccessible(true);
                        $method->invoke($method->isStatic() ? null : $model, $model);
                    }, $instance->priority);
                }
            }
        }

        // Call boot method on the model itself if defined
        if ($reflection->hasMethod("boot")) {
            $reflection->getMethod("boot")->invoke(null);
        }

        // Call boot methods on all traits
        foreach ($reflection->getTraits() as $trait) {
            $traitName = $trait->getName();
            $bootMethod = 'boot' . Utils::classBasename($traitName);

            if ($reflection->hasMethod($bootMethod)) {
                $reflection->getMethod($bootMethod)->invoke(null);
            }
        }
    }

    /**
     * Static method to process the column attributes
     * @param string $class the Model it refers to
     * @param string $propertyName the name of the property in the Model
     * @param string $propertyType the type of the property in the Model
     * @param \ReflectionProperty $property the ReflectionProperty instance
     * @param array $attributes the parsed attributes for this property
     */
    protected static function processColumn(string $class, string $propertyName, string $propertyType, ReflectionProperty $property, array $attributes)
    {
        if (isset($attributes[Column::class])) {
            static::$modelMetas[$class]['columns'][$propertyName] = [
                "property" => $property,
                "type" => $propertyType,
                "attributes" => $attributes
            ];

            if (isset($attributes[Primary::class])) {
                static::$modelMetas[$class]['primary'] = $propertyName;
            }
        }
    }

    /**
     * Static method to process the computed attributes
     * @param string $class the Model it refers to
     * @param string $propertyName the name of the property in the Model
     * @param array $attributes the parsed attributes for this property
     * @param \ReflectionClass $reflection the ReflectionClass instance
     */
    protected static function processComputed(string $class, string $propertyName, array $attributes, ReflectionClass $reflection)
    {
        if (isset($attributes[Computed::class])) {
            $computeMethod = Str::toCamelCase("compute {$propertyName}");
            if (!$reflection->hasMethod($computeMethod))
                throw new MissingComputedResolverException(static::class, $propertyName, $computeMethod);
            static::$modelMetas[$class]['computed'][] = [$propertyName, $computeMethod];
        }
    }


    /*
    
                        ACCESSING METAS
        (Getters to use the columns, computeds, timestamps, ...)

    */

    /**
     * Static method that return the table mapped to this model in the database.
     * Must be override to change the table name.
     * @return string the table name.
     */
    protected static function tableName(): string
    {
        // Return the table name from the props if isset
        if (static::$tableName)
            return static::$tableName;
        // Generate the table name from the model class name
        $class = Utils::classBasename(static::class);
        $snake = Str::toSnakeCase($class);
        $table = Str::pluralize($snake);
        return $table;
    }

    /**
     * Method that returns the Application's Database instance
     * @return Database
     */
    protected static function getDatabase(): Database
    {
        return App::getInstance()->getDatabase();
    }

    /**
     * Method that returns the Model's column(s).
     */
    protected static function getColumns(): array
    {
        return static::$modelMetas[static::class]['columns'];
    }

    /**
     * Method that returns the Model's computed attribute(s)
     */
    protected static function getComputed(): array
    {
        return static::$modelMetas[static::class]['computed'];
    }



    /*

            CONSTRUCT / INIT INSTANCE
        (Hydrate, perform computes, load relations)

    */

    public function __construct(?array $data = null)
    {
        static::bootModel();
        if ($data === null)
            return;
        $this->fill($data);
        $this->computeProperties();
    }


    /**
     * Method to populate an instance of the Model.
     * @param array $data an associative array with the columns to fill.
     */
    protected function fill(array $data): void
    {
        foreach (static::getColumns() as $key => $meta) {

            $colName = Str::toSnakeCase($key);

            if (!array_key_exists($colName, $data))
                continue;

            $value = $data[$colName];
            $type = $meta['type'];

            if ($type == DateTime::class && is_string($value))
                $value = new DateTime($value);

            $this->$key = $value;
        }
    }

    /**
     * Method to compute all the computed properties when the Model has been populated
     * @return void
     */
    protected function computeProperties(): void
    {
        foreach (static::getComputed() as $computed) {
            [$name, $method] = $computed;
            $value = $this->$method();
            $this->$name = $value;
        }
    }

    /*

                Define Model Hooks
        (find, create, update, delete, save)

    */

    public static function registerHook(string $event, callable $callback, int $priority = 0): void
    {
        $class = static::class;

        static::$hooks[$class] ??= [];
        static::$hooks[$class][$event] ??= [];
        static::$hooks[$class][$event][] = [
            "callback" => $callback,
            "priority" => $priority
        ];

        usort(static::$hooks[$class][$event], fn($a, $b) => $b['priority'] <=> $a['priority']);
    }

    protected function runHooks(string $event): void
    {
        $class = static::class;
        $hooks = static::$hooks[$class][$event] ?? [];

        foreach ($hooks as $hook) {
            $hook['callback']($this);
        }
    }


    /*

                CRUD OPERATIONS
        (find, create, update, delete, save)

    */

    public function isPersisted()
    {
        return $this->persisted;
    }

    public static function create(array $data): static
    {
        static::bootModel();
        $instance = new static($data);
        $instance->save();
        return $instance;
    }

    public static function createMany()
    {

    }

    public static function find(int $id): static|null
    {
        $data = static::query()->where('id', '=', $id)->first();
        return $data ? new static($data) : null;
    }

    public static function findBy(string $column, string $value)
    {
        $data = static::query()->where($column, '=', $value)->first();
        return $data ? new static($data) : null;
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

    public function merge()
    {

    }

    public function save()
    {
        $this->runHooks('beforeSave');

        $columns = $this->getColumns();
        $data = [];

        foreach ($columns as $propertyName => $meta) {
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

            $primaryKey = static::$modelMetas[static::class]['primary'] ?? 'id';

            if ($primaryKey && !isset($this->$primaryKey)) {
                $this->$primaryKey = $id;
            }
            $this->presisted = true;
            $this->runHooks('onCreate');
        } else {
            // Update (a faire)
        }

        $this->runHooks('afterSave');

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


    /*

            QUERY BUILDER LOCAL INSTANCE

    */


    /**
     * Method that returns a QueryBuilder instance for this model table
     * @return QueryBuilder
     */
    public static function query(): QueryBuilder
    {
        $builder = static::getDatabase()->query();
        $builder->from(static::tableName());
        return $builder;
    }


    /*

        MAGIC GETTERS / SETTERS

    */


    public function __get($key)
    {
        return $this->$key ?? null;
    }

}