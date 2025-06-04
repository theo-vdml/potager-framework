<?php

namespace Potager\Limpid;

use Potager\App;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;

/**
 * Class Database
 * Wrapper around the Pixie Query Builder and PDO connection.
 * Handles database connection initialization and provides static query interface.
 */
class Database
{
    /** @var \PDO */
    protected \PDO $pdo;

    /** @var Connection */
    protected Connection $connection;

    /**
     * Database constructor.
     * Initializes the Pixie database connection.
     */
    public function __construct()
    {
        $this->initializeConnection();
    }

    /**
     * Initializes the Pixie Connection with configuration values from the App.
     *
     * @throws \Exception If connection fails.
     */
    private function initializeConnection(): void
    {
        // Get the Config instance 
        $config = App::useConfig();

        // Build the config array for Pixie
        $pixieConfig = $this->buildPixieConfig($config);

        // Attempt to connect using config 
        try {
            $this->connection = new Connection($pixieConfig['driver'], $pixieConfig);
            $this->pdo = $this->connection->getPdoInstance();
        } catch (\Exception $e) {
            throw new \RuntimeException("Database connection failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Builds the Pixie-compatible configuration array from App config.
     *
     * @param mixed $config
     * @return array
     */
    private function buildPixieConfig($config): array
    {
        return [
            'driver' => $config->get('database.driver', 'mysql'),
            'host' => $config->get('database.host', 'localhost'),
            'database' => $config->get('database.database'),
            'username' => $config->get('database.username'),
            'password' => $config->get('database.password', ''),
            'charset' => $config->get('database.charset', 'utf8'),
            'collation' => $config->get('database.collation', 'utf8_unicode_ci'),
            'prefix' => $config->get('database.prefix', ''),
            'options' => [\PDO::ATTR_TIMEOUT => 5, \PDO::ATTR_EMULATE_PREPARES => false],
        ];
    }

    /**
     * Returns the raw PDO instance.
     *
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    /**
     * Allows direct access to the Pixie Connection instance.
     *
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * Returns the raw PDO instance.
     *
     * @return \PDO
     */
    public static function pdo(): \PDO
    {
        return App::useDatabase()->getPdo();
    }

    /**
     * Allows direct access to the Pixie Connection instance.
     *
     * @return Connection
     */
    public static function connection(): Connection
    {
        return App::useDatabase()->getConnection();
    }

    /**
     * Returns a query builder instance, optionally pre-set with a table.
     *
     * @return QueryBuilderHandler
     */
    public static function query(): QueryBuilderHandler
    {
        $queryBuilder = App::useDatabase()->connection->getQueryBuilder();
        return $queryBuilder;
    }

    /**
     * Shortcut to create a query builder with a specific table.
     *
     * @param string $table
     * @return QueryBuilderHandler
     */
    public static function table(string $table): QueryBuilderHandler
    {
        return static::query()->table($table);
    }
}
