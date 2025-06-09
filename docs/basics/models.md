---
outline: deep
---

# Models

Limpid models are PHP classes that use attributes to define their structure and behavior. They provide a clean and expressive way to interact with database tables. Each model corresponds to a specific table and enables you to perform CRUD (Create, Read, Update, Delete) operations with minimal boilerplate.

## Defining a Model

To define a model, extend the `Potager\Limpid\Model` base class and annotate properties using attributes like `#[Column]` or `#[Computed]`:

```php
use Potager\Limpid\Model;

class User extends Model {

    #[Column]
    public int $id;

    #[Column]
    public string $name;

    #[Column]
    public string $email;

}
```

## Columns

Every column you want to map from a table to a model must be defined as a property annotated with the `#[Column]` attribute.

> **Note:** Defining columns in a model does not modify the database schema. You are responsible for ensuring your database tables match your model definitions, typically via migrations.

### Defining Columns

```php
use Potager\Limpid\Model;

class User extends Model {

    #[Column]
    public int $id;

    #[Column]
    public string $firstName;

    #[Column]
    public string $lastName;

    #[Column]
    public string $email;

    #[Column]
    public int $role;

    #[Column]
    public array $tags;

}
```

### Column Names

By default, Limpid assumes that your database uses `snake_case` column names. It automatically converts the property name from `camelCase` to `snake_case` when performing database queries.

For example:

```php
#[Column]
public int $id;           // Maps to 'id'

#[Column]
public string $firstName; // Maps to 'first_name'

#[Column]
public string $lastName;  // Maps to 'last_name'
```

To specify a custom column name, pass it to the `#[Column]` attribute:

```php
#[Column(name: 'user_first_name')]
public string $firstName; // Maps to 'user_first_name'
```

### Primary Key

By default, Limpid assumes that the column named `id` is the primary key of the model. If your table uses a different primary key column, or you don’t have an `id` column, you can explicitly define the primary key using the `primary` parameter on the `#[Column]` attribute:

```php
#[Column(primary: true)]
public int $userId;
```

> **Important:** Limpid supports only one primary key per model. If your model defines multiple primary keys or none at all, it will result in an error.

### Preparing and Consuming Column Data

Limpid allows you to transform data before it's saved to the database (prepare) and after it's retrieved (consume). This is useful for handling complex or unsupported types or applying custom logic.

There are two ways to define these transformations:

1. **Using `prepare` and `consume` method references** in the attribute.
2. **Using a reusable `DataTransformer` class**.

#### Method-based Transformation

Define public, non-static methods in the model for preparing and consuming, then reference them in the attribute:

```php
#[Column(consume: 'consumeObject', prepare: 'prepareObject')]
public MyObject $object;

public function consumeObject(mixed $value): MyObject {
    return new MyObject($value);
}

public function prepareObject(MyObject $value): mixed {
    return $value->toString();
}
```

#### DataTransformer-based Transformation

Implement the `Potager\Limpid\DataTransformer` interface, which requires `consume` and `prepare` methods. This approach is reusable across multiple models and fields.

```php
use Potager\Limpid\DataTransformer;

class MyObjectTransformer implements DataTransformer {
    public function consume(mixed $value): MyObject {
        return new MyObject($value);
    }

    public function prepare(MyObject $value): mixed {
        return $value->toString();
    }
}
```

Apply the transformer to a column:

```php
#[Column(transformer: new MyObjectTransformer())]
public MyObject $object;
```

> **Note:** If both `prepare`/`consume` method names and a `transformer` are defined, the methods take precedence. If neither is provided, Limpid will fall back to a default transformer based on the property's type.

### Default Transformers

Limpid comes with built-in support for common data types that are not natively supported by many databases. Limpid will automatically apply a default transformer based on the property's type.

> **Note:** These default transformers are only used when no `prepare`, `consume`, or `transformer` is explicitly provided for the column.

The following default transformers are included:

#### `bool` Support

Booleans are stored as integers in the database (`1` for `true`, `0` for `false`).

```php
#[Column]
public bool $isActive;
```

-   **prepare**: Converts `true`/`false` to `1`/`0`.
-   **consume**: Converts `1`/`0` to `true`/`false`.

#### `array` Support

Arrays are automatically encoded to and decoded from JSON, making it easy to store structured data in a single text column.

```php
#[Column]
public array $tags;
```

-   **prepare**: Converts the array to a JSON string.
-   **consume**: Parses a JSON string back into an array.

#### `DateTime` Support

When mapping `DateTime` objects, Limpid automatically converts them to and from string representations compatible with typical SQL date/time formats.

```php
// Saved to database as: "2025-06-07 15:30:00"
// Fetched from database as: DateTime instance

#[Column]
public DateTime $createdAt;
```

-   **prepare**: Converts `DateTime` to `Y-m-d H:i:s` string.
-   **consume**: Parses a string into a `DateTime` object.

## Computed Properties

Computed properties in Limpid allow you to define dynamic, read-only values that are derived from other data in the model. These properties are **not stored in the database** and are **re-evaluated each time you access them**.

### Defining a Computed Property

To define a computed property, annotate the property with the `#[Computed]` attribute. Then, provide a method in your model to compute the value. The resolver method must be a public non-static method that returns the computed value.

There are **two ways** to define the resolver method:

#### Using the Default Naming Convention

If no resolver is specified, Limpid will look for a method following the pattern:
**`compute{PropertyNameInPascalCase}`**

Example:

```php{12-17}
class User extends Model
{
    #[Column]
    public ?int $id;

    #[Column]
    public ?string $firstName;

    #[Column]
    public ?string $lastName;

    #[Computed]
    public string $fullName;

    public function computeFullName(): string {
        return $this->firstName . ' ' . $this->lastName;
    }
}
```

> For the property `$fullName`, the expected method name is `computeFullName`.

#### Using a Custom Resolver Method

You can explicitly define the resolver method using the `resolver` parameter:

```php
class User extends Model
{
    #[Column]
    public ?int $id;

    #[Column]
    public ?string $firstName;

    #[Column]
    public ?string $lastName;

    #[Computed] // [!code --]
    #[Computed(resolver: 'resolveFullName')] // [!code ++]
    public string $fullName;

    public function computeFullName(): string { // [!code --]
    public function resolveFullName(): string { // [!code ++]
        return $this->firstName . ' ' . $this->lastName;
    }
}
```

This approach is useful when your method name doesn't follow the default convention.

> **Note**
>
> -   Computed properties **cannot be written to** — they are read-only by design.
> -   The value of a computed property is **re-evaluated every time you access it**.
> -   Computed values are **not included in database operations** like inserts or updates.

## Model Configuration

### Table Name

By default, Limpid will automatically determine the table name by converting the model class name to `snake_case` and pluralizing it.

For example, a model named `UserProfile` would map to the table `user_profiles`.

If you want to explicitly specify a custom table name, you can use the `#[Table]` attribute on the class:

```php{1}
#[Table('user_table')]
class User extends Model
{
    #[Column]
    public ?int $id;

    // ...
}
```

> This is useful when your table name doesn't follow the default naming convention or when working with legacy databases.

### Self-Assigning Primary Keys

By default, Limpid expects the database to generate primary key values (e.g., auto-incrementing integers). If you prefer to assign primary keys yourself—such as using UUIDs—you can enable self-assignment by setting the following static property on your model:

```php{3}
class User extends Model
{
    public static bool $selfAssignPrimaryKey = true;

    // ...
}
```

With this enabled, **you must manually set a value for the primary key** before persisting the model.

To automate this, you can define a `beforeCreate` lifecycle hook in your model:

```php{7-11}
class User extends Model
{
    public static bool $selfAssignPrimaryKey = true;

    // ...

    #[Hook('beforeCreate')]
    public function setPrimaryKey(): void
    {
        $this->uuid = random_uuid();
    }
}
```

> This is especially useful when using UUIDs or custom keys instead of relying on the database’s auto-increment behavior.

### Manually Assigning a Primary Key (One-Time)

Limpid protects primary keys from being accidentally modified at runtime. If your model is **not** in self-assign mode (`$selfAssignPrimaryKey = false`), any direct assignment to the primary key will be silently ignored to prevent unintended behavior.

However, if you need to manually assign a primary key once—such as when importing data or restoring a record—you can explicitly disable this protection:

```php
$user = new User();

// Attempting to set the primary key directly will be ignored
$user->id = 99; // Ignored due to primary key protection

// Temporarily disable the protection
$user->disablePrimaryKeyProtection();
$user->id = 99; // This assignment is now accepted
```

> Use this method cautiously. It's intended for advanced use cases where you have full control over the integrity of the primary key values.

After assigning the value, you can re-enable the protection to avoid accidental changes later in the code:

```php
$user->enablePrimaryKeyProtection();
```

## Model Hooks

Model hooks allow you to attach logic to specific points in a model’s lifecycle. They’re a clean and centralized way to encapsulate behaviors—such as data transformation, logging, or validation—without scattering that logic across your application.

Hooks are defined as public, non-static methods on your model and registered using the `#[Hook]` attribute. They are automatically triggered by Limpid when the associated lifecycle event occurs.

Suppose you want to ensure passwords are always hashed before being saved to the database. You can do this using the `beforeSave` hook:

```php{13-18}
class User extends Model {
    #[Column]
    public int $id;

    #[Column]
    public string $email;

    #[Column]
    public string $password;

    // ...

    #[Hook('beforeSave')]
    public function hashPasswordOnSave(User $user): void {
        if ($user->isDirty('password')) {
            $user->password = password_hash($user->password, PASSWORD_DEFAULT);
        }
    }
}
```

> In this example, the hook checks if the `password` field was modified (`isDirty('password')`) and re-hashes it before the save operation is finalized.

### Available Hooks

| Hook Name      | Description                                                                | Arguments      |
| -------------- | -------------------------------------------------------------------------- | -------------- |
| `beforeSave`   | Called before saving the model (both create and update).                   | `Model $model` |
| `afterSave`    | Called after the model has been saved (both create and update).            | `Model $model` |
| `beforeCreate` | Triggered before a new model is inserted into the database.                | `Model $model` |
| `afterCreate`  | Triggered after a new model has been inserted into the database.           | `Model $model` |
| `beforeUpdate` | Called before an existing model is updated.                                | `Model $model` |
| `afterUpdate`  | Called after an existing model has been updated.                           | `Model $model` |
| `onClone`      | Invoked when the model is cloned. Useful for resetting or cleaning fields. | `Model $model` |

## Automatic Timestamp Management

Limpid provides built-in support for managing timestamps on your models—automatically and without any additional configuration. When enabled, it will automatically set the `created_at` and `updated_at` columns whenever a model is created or updated.

To use this feature, simply ensure that your database table includes the `created_at` and `updated_at` columns (typically defined in your migrations), then apply the `WithTimestamps` trait to your model:

```php{2,5}
use Potager\Limpid\Model;
use Potager\Limpid\Traits\WithTimestamps;

class User extends Model {
    use WithTimestamps;

    #[Column]
    public int $id;

    #[Column]
    public string $name;

    #[Column]
    public string $email;
}
```

Once configured, Limpid will automatically handle the timestamps for you. You can access the timestamp values using camelCase property names:

```php{2,3}
$user = User::find(1);
echo $user->createdAt; // Outputs the creation timestamp
echo $user->updatedAt; // Outputs the last update timestamp
```

> **Note:** The `created_at` and `updated_at` columns must be of type `DATETIME` or `TIMESTAMP` in your database schema. Limpid will map these columns to the `createdAt` and `updatedAt` properties on your model automatically.
