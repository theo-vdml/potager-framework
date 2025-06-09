---
outline: deep
---

# CRUD Operations

Limpid Models provide a clean, intuitive API for performing basic **Create**, **Read**, **Update**, and **Delete** operations. Along with standard database interaction methods, Limpid includes powerful features such as **dirty tracking**, **primary key protection**, and **model state inspection** (`isPersisted()`, `isDeleted()`).

This guide walks you through all aspects of CRUD functionality using a `User` model example.

## Create

You can create and save a new model using either the database column names or the class property names.

### Using Column Names

```php
$user = User::create([
    'first_name' => 'John',
    'name' => 'Doe',
    'email' => 'john@doe.com'
]);
```

### Using Property Names (CamelCase)

```php
$user = User::create([
    'firstName' => 'John',
    'lastName' => 'Doe',
    'email' => 'john@doe.com'
]);
```

### Creating a Draft (Unsaved Model)

```php
$user = new User();
$user->firstName = 'John';
$user->lastName = 'Doe';
$user->email = 'john@doe.com';
```

### Saving a Draft

```php
$user->save(); // Persists the model to the database
```

## Read

### Find by Primary Key

```php
$user = User::find(1);
```

### Find by Custom Column

```php
$user = User::findBy('email', 'bob@doe.com');
```

If no matching record is found, `null` is returned:

```php
$user = User::find(999); // null
```

## Update

You can update a model by changing its properties and calling `save()`:

```php
$user = User::findBy('email', 'alice@doe.com');
$user->email = 'alice.doe@mail.net';
$user->save();
```

## Advanced: Primary Key Updates

By default, Limpid protects primary keys. You can override this if needed:

### Disable Primary Key Protection

```php
$user = new User();
$user->disablePrimaryKeyProtection();
$user->id = 99;
$user->firstName = 'Manual';
$user->lastName = 'ID';
$user->email = 'manual@id.com';
$user->save();
```

### Enable Again (optional)

```php
$user->enablePrimaryKeyProtection();
```

> ⚠️ Modifying primary keys is discouraged in most applications unless you have a specific use case.

## Delete

You can delete models with the `delete()` method.

### Deleting a Persisted Model

```php
$user = User::findBy('email', 'alice@doe.com');
$user->delete();
```

### Deleting a Recently Created Model

```php
$user = User::create([
    'firstName' => 'John',
    'lastName' => 'Doe',
    'email' => 'john@doe.com'
]);
$user->delete();
```

## Exception Handling

Limpid provides helpful exceptions when invalid operations occur:

### Attempt to Save Without Primary Key

```php
$user->forceOriginal('id', null);
$user->save();

// Throws: RuntimeException: "Cannot update model without primary key."
```

### Update Fails (0 Rows Affected)

```php
$user->forceOriginal('id', 999);
$user->save();

// Throws: RuntimeException: "Failed to update model"
```

### Deleting a Model Without Persistence

```php
$user = new User();
$user->delete();

// Throws: RuntimeException: "Cannot delete a model that has not been persisted."
```

### Deleting an Already Deleted Model

```php
$user = User::findBy('email', 'alice@doe.com');
$user->delete();
$user->delete();

// Throws: RuntimeException: "Cannot perform operation on a deleted model."
```

## Model State Inspection

### Checking if a Model is Persisted

Use `isPersisted()` to check whether the model exists in the database.

```php
$user = new User();
$user->isPersisted(); // false

$user = User::find(1);
$user->isPersisted(); // true
```

### Dirty Tracking

Limpid tracks changes to model properties. You can inspect the changes before saving:

#### Check if Model is Dirty

```php
$user->isDirty(); // true or false
```

#### Get Changed Attributes

```php
$user->getDirty();
// ['email' => 'alice.doe@mail.net']
```

This helps you:

-   Avoid unnecessary database writes.
-   Log changes before committing.
-   Conditionally update only modified models.

### Checking if a Model is Deleted

After deletion, you can verify the state with `isDeleted()`:

```php
$user->delete();
$user->isDeleted(); // true
```

## Summary

| Method                          | Purpose                                 |
| ------------------------------- | --------------------------------------- |
| `create([...])`                 | Create and persist a model immediately  |
| `new Model()`                   | Create a draft model (not yet saved)    |
| `save()`                        | Persist changes to database             |
| `find(id)`                      | Retrieve by primary key                 |
| `findBy(col, val)`              | Retrieve by custom column               |
| `delete()`                      | Delete the model                        |
| `isPersisted()`                 | Check if model is saved in the database |
| `isDeleted()`                   | Check if model has been deleted         |
| `isDirty()`                     | Check if model has unsaved changes      |
| `getDirty()`                    | Get list of unsaved attributes          |
| `disablePrimaryKeyProtection()` | Allow changing the primary key          |
