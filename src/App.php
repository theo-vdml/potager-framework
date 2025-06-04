<?php

namespace Potager;

use Potager\Grape\Grape;
use Potager\Mailer\MailManager;
use Potager\Router\Router;
use Potager\Limpid\Database;
use Potager\Session;
use Potager\LatteEngine;

class App
{

    protected static App $app;
    protected Config $config;
    protected Router $router;
    protected Database $database;
    protected MailManager $mailManager;
    protected Session $session;
    protected LatteEngine $latteEngine;

    public function __construct()
    {
        self::$app = $this;
        $this->config = new Config();
        $this->router = new Router();
        $this->database = new Database();
        $this->mailManager = new MailManager();
        $this->session = new Session();

        $this->latteEngine = new LatteEngine();

        $dsn = self::useConfig()->get('database.dsn');
        $user = self::useConfig()->get('database.user');
        $password = self::useConfig()->get('database.password');
        Grape::connectMySQL($dsn, $user, $password);
    }

    public static function getInstance()
    {
        return self::$app;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getMailer(): MailManager
    {
        return $this->mailManager;
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function getLatteEngine()
    {
        return $this->latteEngine;
    }

    public static function useApp()
    {
        return self::$app;
    }

    public static function useConfig(): Config
    {
        return self::$app->getConfig();
    }

    public static function useDatabase()
    {
        return self::$app->getDatabase();
    }

    public static function useRouter()
    {
        return self::$app->getRouter();
    }

    public static function useMail()
    {
        return self::$app->getMailer();
    }

    public static function useSession()
    {
        return self::$app->getSession();
    }

    public static function useLatte()
    {
        return self::$app->getLatteEngine();
    }

}