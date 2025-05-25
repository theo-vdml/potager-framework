<?php

namespace Potager\Grape\Traits;

use Potager\Grape\FieldContext;

trait CanBeUnique
{
    public function unique(string $table, string $column, array $ignores = [])
    {
        $this->rules[] = function (FieldContext $ctx) use ($table, $column, $ignores) {
            $value = $ctx->getValue();

            $query = "SELECT COUNT(*) FROM {$table} WHERE {$column} = ?";
            $params = [$value];

            foreach ($ignores as $ignoreCol => $ignoreVal) {
                $query .= " AND {$ignoreCol} != ?";
                $params[] = $ignoreVal;
            }

            $pdo = $ctx->getPDO();

            $statement = $pdo->prepare($query);

            $statement->execute($params);

            $count = $statement->fetchColumn();

            if ($count > 0)
                $ctx->report("{{ field }} must be unique in {$table}.", "unique");

        };

        return $this;
    }
}