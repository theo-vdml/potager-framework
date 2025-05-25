<?php

namespace Potager;

use Potager\Support\Arr;

class Session
{
    protected string $wrapperSessionKey;
    protected string $flashSessionKey; // 👈 Clé unique a la racine de $_SESSION pour ranger tout les flashs
    protected array $flashBuffer = []; // 👈 Tableau qui contiens la prochaine fournée de flashs
    protected array $flash = [];
    protected bool $registered = false;

    public function __construct(string $wrapperSessionKey = '__session', string $flashSessionKey = '__flash')
    {
        $this->wrapperSessionKey = $wrapperSessionKey;
        $this->flashSessionKey = $flashSessionKey;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->rotateFlash();
    }

    /*====================
        CLASSIC SESSION
    ======================*/

    public function set(string $key, mixed $value)
    {
        Arr::set($_SESSION, "$this->wrapperSessionKey.$key", $value);
        return $this;
    }

    public function has(string $key)
    {
        return Arr::has($_SESSION, "$this->wrapperSessionKey.$key");
    }

    public function get(string $key, mixed $default = null)
    {
        return Arr::get($_SESSION, "$this->wrapperSessionKey.$key", $default);
    }

    public function pull(string $key, mixed $default = null)
    {
        $value = $this->get($key, $default);
        $this->remove($key);
        return $value;
    }

    public function all()
    {
        return $_SESSION[$this->wrapperSessionKey] ?? [];
    }

    public function remove(string $key)
    {
        Arr::forget($_SESSION, "$this->wrapperSessionKey.$key");
        return $this;
    }

    public function clear()
    {
        unset($_SESSION[$this->wrapperSessionKey]);
    }

    public function regenerate(bool $delete_old = false)
    {
        session_regenerate_id($delete_old);
    }

    /*====================
         FLASH SESSION
    ======================*/

    public function flash(string $key, mixed $value)
    {
        Arr::set($this->flashBuffer, $key, $value);
        $this->register();
        return $this;
    }

    public function getFlash(string $key, mixed $default = null)
    {
        return Arr::get($this->flash ?? [], $key, $default);
    }

    public function allFlash()
    {
        return $this->flash ?? [];
    }

    public function preserveFlashes(string ...$keys)
    {
        if (empty($keys)) {
            $this->flashBuffer = array_merge($this->flashBuffer, $this->flash);
        }
    }

    public function commitFlash()
    {
        if (!empty($this->flashBuffer))
            $_SESSION[$this->flashSessionKey] = $this->flashBuffer;
    }

    public function rotateFlash()
    {
        $this->flash = $_SESSION[$this->flashSessionKey] ?? [];
        unset($_SESSION[$this->flashSessionKey]);
    }

    protected function register()
    {
        if ($this->registered)
            return;
        register_shutdown_function(fn() => $this->commitFlash());
        $this->registered = true;
    }
}