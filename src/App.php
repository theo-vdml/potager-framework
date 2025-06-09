<?php

namespace Potager;

use Potager\Auth\Authenticator;
use Potager\Container\Container;
use Potager\Grape\Grape;
use Potager\Limpid\Database;
use Potager\Mailer\MailManager;
use Potager\Router\Router;

/**
 * Class App
 *
 * Main application class implementing a singleton pattern with service container.
 *
 * @method static Router useRouter()
 * @method static Session useSession()
 * @method static MailManager useMailer()
 * @method static Database useDatabase()
 * @method static LatteEngine useLatte()
 * @method static Authenticator useAuth()
 */
class App
{
    /**
     * @var ?App Singleton instance of the App
     */
    protected static ?App $instance = null;

    /**
     * @var ?Container Service container instance
     */
    protected ?Container $container = null;

    /**
     * @var Config Application configuration instance
     */
    protected Config $config;

    /**
     * App constructor.
     *
     * @param Container|null $container Optional custom service container.
     * @throws \Exception If container is needed but not set when registering services.
     */
    public function __construct(?Container $container = null)
    {
        $this->config = new Config();
        $this->container = $container ?? new Container();
        $this->registerMinimalServices();
        $dsn = $this->config->get('database.dsn');
        $user = $this->config->get('database.user');
        $password = $this->config->get('database.password');
        Grape::connectMySQL($dsn, $user, $password);
        Database::initialize($this->config->get('database'));
    }

    /**
     * Registers the minimal required services as singletons if not already registered.
     *
     * @throws \Exception If container is not set.
     * @return void
     */
    protected function registerMinimalServices()
    {
        if (!$this->container) {
            throw new \Exception("Cannot register services without a container set");
        }

        $this->container->singletonIfNotExists('router', fn(): Router => new Router());
        $this->container->singletonIfNotExists('session', fn(): Session => new Session());
        $this->container->singletonIfNotExists('mailer', fn(): MailManager => new MailManager());
        $this->container->singletonIfNotExists('database', fn(): Database => Database::initialize($this->config->get('database')));
        $this->container->singletonIfNotExists('latte', fn(): LatteEngine => new \Potager\LatteEngine());
        $this->container->singletonIfNotExists('auth', fn(): Authenticator => new Authenticator($this->config->get('auth')));
    }

    /**
     * Returns the singleton instance of the App.
     *
     * @param Container|null $container Optional container to initialize the app with.
     * @return self
     */
    public static function getInstance(?Container $container = null): App
    {
        if (self::$instance === null) {
            self::$instance = new self($container);
        }
        return self::$instance;
    }

    /**
     * Get the service container instance.
     *
     * @return Container The service container.
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Get the application configuration instance.
     *
     * @return Config The configuration object.
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Bind a service to the container.
     *
     * @param string $service
     * @param \Closure $builder
     * @return void
     * @throws \Exception If container is not set.
     */
    public function bind(string $service, \Closure $builer): void
    {
        if (!$this->container) {
            throw new \Exception("Cannot register services without a container set");
        }
        $this->container->bind($service, $builer);
    }

    /**
     * Bind a singleton service to the container.
     *
     * @param string $service
     * @param \Closure $builder
     * @return void
     * @throws \Exception If container is not set.
     */
    public function singleton(string $service, \Closure $builer): void
    {
        if (!$this->container) {
            throw new \Exception("Cannot register singleton without a container set");
        }
        $this->container->singleton($service, $builer);
    }

    /**
     * Retrieve a service instance from the container.
     *
     * @param string $service
     * @return mixed
     * @throws \Exception If container is not set or service not found.
     */
    public function get(string $service): mixed
    {
        if (!$this->container) {
            throw new \Exception("Cannot register services without a container set");
        }
        return $this->container->get($service);
    }

    /**
     * Get the Config instance.
     *
     * @return Config
     */
    public static function useConfig(): Config
    {
        return static::getInstance()->getConfig();
    }

    /**
     * Magic method to allow static calls to useXyz() to fetch services.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \BadMethodCallException If service or method is undefined.
     */
    public static function __callStatic($method, $args): mixed
    {
        if (str_starts_with($method, 'use')) {
            $service = lcfirst(substr($method, 3));
            $instance = self::getInstance();

            if (!$instance->container->has($service)) {
                throw new \BadMethodCallException("Undefined service: {$service}");
            }

            return $instance->container->get($service);
        }
        throw new \BadMethodCallException("Undefined static method {$method}");
    }
}