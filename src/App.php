<?php

namespace Potager;

use Potager\Container\Container;
use Potager\Grape\Grape;

class App
{

    protected static ?App $instance = null;
    protected ?Container $container = null;

    protected Config $config;


    public function __construct(?Container $container = null)
    {
        $this->config = new Config();

        if ($container) {
            $this->container = $container;
        } else {
            $this->container = new Container();
            $this->registerServices();
        }


        $dsn = $this->config->get('database.dsn');
        $user = $this->config->get('database.user');
        $password = $this->config->get('database.password');
        Grape::connectMySQL($dsn, $user, $password);
    }


    protected function registerServices()
    {
        if (!$this->container) {
            throw new \Exception("Cannot register services without a container set");
        }

        // Register Router as singleton
        $this->container->singleton('router', function ($container): \Potager\Router\Router {
            return new \Potager\Router\Router();
        });

        // Register Session as singleton
        $this->container->singleton('session', function ($container): \Potager\Session {
            return new \Potager\Session();
        });

        // Register Mail Manager as singleton
        $this->container->singleton('mailer', function ($container): \Potager\Mailer\MailManager {
            return new \Potager\Mailer\MailManager();
        });

        // Register Database as sigleton
        $this->container->singleton('database', function ($container): \Potager\Limpid\Database {
            return new \Potager\Limpid\Database($this->config->get('database'));
        });

        // Register Latte Engine
        $this->container->bind('latteEngine', function ($container): \Potager\LatteEngine {
            return new \Potager\LatteEngine();
        });
    }


    public static function getInstance(?Container $container = null): App
    {
        if (self::$instance === null) {
            self::$instance = new self($container);
        }
        return self::$instance;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getDatabase(): \Potager\Limpid\Database
    {
        return $this->container->get('database');
    }

    public function getRouter(): \Potager\Router\Router
    {
        return $this->container->get('router');
    }

    public function getMailer(): \Potager\Mailer\MailManager
    {
        return $this->container->get('mailer');
    }

    public function getSession(): Session
    {
        return $this->container->get('session');
    }

    public function getLatteEngine()
    {
        return $this->container->get('latteEngine');
    }

    public static function useApp()
    {
        return self::getInstance();
    }

    public static function useConfig(): Config
    {
        return self::getInstance()->getConfig();
    }

    public static function useDatabase()
    {
        return self::getInstance()->getDatabase();
    }

    public static function useRouter()
    {
        return self::getInstance()->getRouter();
    }

    public static function useMail()
    {
        return self::getInstance()->getMailer();
    }

    public static function useSession()
    {
        return self::getInstance()->getSession();
    }

    public static function useLatte()
    {
        return self::getInstance()->getLatteEngine();
    }

}