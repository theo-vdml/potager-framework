<?php

namespace Potager\Limpid;

use Exception;
use PDO;

class QueryBuilder
{
    public PDO $pdo;
    protected string $table;
    protected array $columns = ['*'];
    protected array $wheres = [];
    protected ?int $limit = null;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function from(string $table): self
    {
        return $this->table($table);
    }

    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function select(array $columns): self
    {
        $this->columns = $columns;
        return $this;
    }

    public function where(string $column, string $operator, string $value): self
    {
        $this->wheres[] = [$column, $operator, $value];
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function get(): array
    {
        $colums = implode(", ", $this->columns);
        $sql = "SELECT {$colums} FROM {$this->table}";

        if ($this->wheres) {
            $conditions = array_map(fn($w): string => "{$w[0]} {$w[1]} ?", $this->wheres);
            $wheres = implode(' AND ', $conditions);
            $sql .= " WHERE {$wheres}";
        }

        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
        }

        $values = array_column($this->wheres, 2);

        $statement = $this->pdo->prepare($sql);
        $statement->execute($values);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    public function insert(array $data): int
    {
        $columns = array_keys($data);
        $values = array_values($data);

        $columnsList = implode(', ', $columns);
        $placeholders = implode(', ', array_fill(0, count($values), '?'));

        $sql = "INSERT INTO {$this->table} ({$columnsList}) VALUES ({$placeholders})";

        $statement = $this->pdo->prepare($sql);
        $statement->execute($values);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(array $data): void
    {
        $columns = array_keys($data);
        $values = array_values($data);

        $setClauses = implode(', ', array_map(fn($col) => "{$col} = ?", $columns));

        if (empty($this->wheres))
            throw new Exception("Cannot update without WHERE clause(s)");

        $whereClauses = implode(' AND ', array_map(fn($w): string => "{$w[0]} {$w[1]} ?", $this->wheres));
        $whereValues = array_column($this->wheres, 2);

        $sql = "UPDATE {$this->table} SET {$setClauses} WHERE {$whereClauses}";

        $statement = $this->pdo->prepare($sql);
        $statement->execute([...$values, ...$whereValues]);
    }

    public function delete()
    {
        if (empty($this->wheres))
            throw new Exception("Cannot delete without WHERE clause(s)");

        $whereClauses = implode(' AND ', array_map(fn($w): string => "{$w[0]} {$w[1]} ?", $this->wheres));
        $whereValues = array_column($this->wheres, 2);

        $sql = "DELETE FROM {$this->table} WHERE {$whereClauses}";

        $statement = $this->pdo->prepare($sql);
        $statement->execute($whereValues);
    }

}