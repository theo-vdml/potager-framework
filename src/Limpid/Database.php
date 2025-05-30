<?php

namespace Potager\Limpid;

use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;
use Potager\App;

class Database
{
    protected \PDO $pdo;
    protected Connection $connection;

    public function __construct()
    {
        $this->initPixie();
    }

    private function initPixie()
    {
        $config = App::useConfig();

        $pixieConfig = [
            'driver' => $config->get('database.driver', 'mysql'), // Db driver
            'host' => $config->get('database.host', 'localhost'),
            'database' => $config->get('database.database'),
            'username' => $config->get('database.username'),
            'password' => $config->get('database.password', ''),
            'charset' => $config->get('database.charset', 'utf8'), // Optional
            'collation' => $config->get('database.collation', 'utf8_unicode_ci'), // Optional
            'prefix' => $config->get('database.prefix', ''), // Table prefix, optional
            'options' => [
                \PDO::ATTR_TIMEOUT => 5,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ],
        ];

        $this->connection = new Connection($pixieConfig['driver'], $pixieConfig);
    }

    public function pdo()
    {
        return $this->connection->getPdoInstance();
    }

    public static function query(?string $table = null): QueryBuilderHandler
    {
        $qb = App::useDatabase()->connection->getQueryBuilder();
        if ($table)
            $qb->table($table);
        return $qb;
    }

    public static function table(string $table)
    {
        return static::query($table);
    }
}