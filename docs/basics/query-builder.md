---
outline: [2, 3]
---

# Query Builder

Potager ship a fluent query builder that allows you to build SQL queries in a more readable and maintainable way. It is designed to be simple and intuitive, making it easy to construct complex queries without writing raw SQL.

## Get a Query Builder Instance

To get a query builder instance you can use the static `query()` or `from()` methods on the `Database` class.

```php
// Get a query builder instance
$query = Database::query();

// Get a query builder instance with a specific table already set
$queryWithTable = Database::from('users');
```

## Methods & Properties

### select

The `select()` method is used to specify the columns that you want to retrieve from the database. You can either pass an array of column names or pass them as multiple arguments.

```php
// Using an array of column names
Database::from('user')
    ->select(['id', 'name']);

// Using multiple arguments
Database::from('user')
    ->select('id', 'name');
```

You can define aliases for the columns using the `as` expression or by passing an assiciative array where the key is the alias and the value is the column name.

```php
// Using 'as' expression
Database::from('user')
    ->select('id', 'name as user_name');

// Using associative array
Database::from('user')
    ->select('id', ['name' => 'user_name']);
```

### from

The `from()` method is used to specify the database table that you want to query.

```php
Database::query()
    ->from('users');
```

The query builder also supports derived tables, by passsing a subquery or a closure (wich act as a subquery) to the `from()` method.

```php
// Using a subquery
Database::from(
    Database::from('users')
        ->select('id', 'name');
);

// Using a closure
Database::from(function ($query) {
    $query->from('users')
        ->select('id', 'name');
});
```

You can alias the table by passing a second parameter to the `from()` method.

::: code-group

```php [Query Builder]
Database::from('users', 'u')
    ->select('u.id', 'u.name');
```

```sql [Generated SQL]
SELECT u.id, u.name FROM `users` as u;
```

:::

### where

The `where()` method is used to define the `WHERE` clause in your SQL queries. This method accepts a variety of parameters to let you build complex conditions with ease.

Using the column name as the first parameter and its value as the second parameter, you can create a simple equality condition.

::: code-group

```php [Query Builder]
Database::from('users')
    ->where('id', 1);
```

```sql [Generated SQL]
SELECT * FROM `users` WHERE `id` = ?;
```

:::

You can also specify a SQL operator as the second parameter to create a condition with a different operator.

::: code-group

```php [Query Builder]
Database::from('users')
    ->where('id', '>', 5);
```

```sql [Generated SQL]
SELECT * FROM `users` WHERE `id` > ?;
```

:::

You can also define where groups by passing a closure to the `where()` method.

::: code-group

```php [Query Builder]
Database::from('users')
    ->where(function ($query) {
        $query->where('id', 5)
            ->orWhere('id', 10);
    })->where('name', 'like', '%john%');
```

```sql [Generated SQL]
SELECT * FROM `users`
WHERE (`id` = ? OR `id` = ?)
AND `name` LIKE ?;
```

:::

### orWhere

The `orWhere()` method works similarly to `where()`, but it joins the condition with an `OR` logical operator.

::: code-group

```php [Query Builder]
Database::from('users')
    ->where('status', 'active')
    ->orWhere('age', '>', 60);
```

```sql [Generated SQL]
SELECT * FROM `users` WHERE `status` = ? OR `age` > ?;
```

:::

### whereNull / orWhereNull

These methods are used to add a `WHERE column IS NULL` or `OR WHERE column IS NULL` condition to your query.

::: code-group

```php [Query Builder]
// WHERE column IS NULL
Database::from('users')
    ->whereNull('deleted_at');

// OR WHERE column IS NULL
Database::from('products')
    ->where('status', 'available')
    ->orWhereNull('description');
```

```sql [Generated SQL]
SELECT * FROM `users` WHERE `deleted_at` IS NULL;
SELECT * FROM `products` WHERE `status` = ? OR `description` IS NULL;
```

:::

### whereNotNull / orWhereNotNull

These methods are used to add a `WHERE column IS NOT NULL` or `OR WHERE column IS NOT NULL` condition to your query.

::: code-group

```php [Query Builder]
// WHERE column IS NOT NULL
Database::from('users')
    ->whereNotNull('email_verified_at');

// OR WHERE column IS NOT NULL
Database::from('orders')
    ->where('total', '>', 0)
    ->orWhereNotNull('shipped_at');
```

```sql [Generated SQL]
SELECT * FROM `users` WHERE `email_verified_at` IS NOT NULL;
SELECT * FROM `orders` WHERE `total` > ? OR `shipped_at` IS NOT NULL;
```

:::

### whereIn / orWhereIn

These methods add a `WHERE column IN (...)` or `OR WHERE column IN (...)` condition. They expect an array of values for the `IN` clause.

::: code-group

```php [Query Builder]
// WHERE column IN (values)
Database::from('products')
    ->whereIn('category_id', [1, 5, 8]);

// OR WHERE column IN (values)
Database::from('users')
    ->where('status', 'pending')
    ->orWhereIn('role_id', [2, 3]);
```

```sql [Generated SQL]
SELECT * FROM `products` WHERE `category_id` IN (?, ?, ?);
SELECT * FROM `users` WHERE `status` = ? OR `role_id` IN (?, ?);
```

:::

### whereNotIn / orWhereNotIn

These methods add a `WHERE column NOT IN (...)` or `OR WHERE column NOT IN (...)` condition. They also expect an array of values.

::: code-group

```php [Query Builder]
// WHERE column NOT IN (values)
Database::from('products')
    ->whereNotIn('category_id', [10, 11]);

// OR WHERE column NOT IN (values)
Database::from('users')
    ->where('is_active', true)
    ->orWhereNotIn('id', [1, 2, 3]);
```

```sql [Generated SQL]
SELECT * FROM `products` WHERE `category_id` NOT IN (?, ?);
SELECT * FROM `users` WHERE `is_active` = ? OR `id` NOT IN (?, ?, ?);
```

:::

### whereBetween / orWhereBetween

These methods add a `WHERE column BETWEEN value1 AND value2` or `OR WHERE column BETWEEN value1 AND value2` condition. They expect an array with exactly two values.

::: code-group

```php [Query Builder]
// WHERE column BETWEEN value1 AND value2
Database::from('orders')
    ->whereBetween('total', [50, 200]);

// OR WHERE column BETWEEN value1 AND value2
Database::from('products')
    ->where('stock', '>', 0)
    ->orWhereBetween('price', [10.00, 25.50]);
```

```sql [Generated SQL]
SELECT * FROM `orders` WHERE `total` BETWEEN ? AND ?;
SELECT * FROM `products` WHERE `stock` > ? OR `price` BETWEEN ? AND ?;
```

:::

### whereNotBetween / orWhereNotBetween

These methods add a `WHERE column NOT BETWEEN value1 AND value2` or `OR WHERE column NOT BETWEEN value1 AND value2` condition. Like `whereBetween`, they expect an array with exactly two values.

::: code-group

```php [Query Builder]
// WHERE column NOT BETWEEN value1 AND value2
Database::from('orders')
    ->whereNotBetween('total', [500, 1000]);

// OR WHERE column NOT BETWEEN value1 AND value2
Database::from('products')
    ->where('is_available', false)
    ->orWhereNotBetween('weight', [10, 50]);
```

```sql [Generated SQL]
SELECT * FROM `orders` WHERE `total` NOT BETWEEN ? AND ?;
SELECT * FROM `products` WHERE `is_available` = ? OR `weight` NOT BETWEEN ? AND ?;
```

:::
