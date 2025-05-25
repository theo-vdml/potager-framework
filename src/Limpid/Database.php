<?php

namespace Potager\Limpid;

use Potager\App;
use PDO;

class Database
{
    protected PDO $pdo;

    public function __construct()
    {
        $config = App::useConfig();

        $dsn = $config->get('database.dsn');
        $user = $config->get('database.user');
        $password = $config->get('database.password');

        $this->pdo = new PDO($dsn, $user, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function pdo()
    {
        return $this->pdo;
    }

    public function query(?string $table = null): QueryBuilder
    {
        $builder = new QueryBuilder($this->pdo());
        if ($table)
            $builder->table($table);
        return $builder;
    }
}