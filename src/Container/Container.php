<?php

namespace Potager\Container;

use Closure;
use Exception;

/**
 * Class Container
 *
 * A simple dependency injection container that manages class dependencies and their instantiations.
 */
class Container
{
    /**
     * @var array
     * Stores the closures for creating service instances.
     */
    protected $services = [];

    /**
     * @var array
     * Stores the singleton instances of services.
     */
    protected $instances = [];

    /**
     * Binds a service name to a closure that can create an instance of the service.
     *
     * @param string $name The name of the service to bind.
     * @param Closure $closure A closure that returns an instance of the service.
     */
    public function bind(string $name, Closure $closure)
    {
        $this->services[$name] = $closure;
    }

    /**
     * Binds a singleton service name to a closure.
     * The service instance will be created only once and reused on subsequent calls.
     *
     * @param string $name The name of the singleton service to bind.
     * @param Closure $closure A closure that returns an instance of the service.
     */
    public function singleton(string $name, Closure $closure)
    {
        $this->services[$name] = $closure;
        $this->instances[$name] = null;
    }

    /**
     * Retrieves an instance of the specified service.
     *
     * @param string $name The name of the service to retrieve.
     * @return mixed The instance of the requested service.
     * @throws Exception If the service is not registered in the container.
     */
    public function get(string $name)
    {
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        if (isset($this->services[$name])) {
            $service = $this->services[$name];
            $instance = $service($this);
            if (array_key_exists($name, $this->instances)) {
                $this->instances[$name] = $instance;
            }
            return $instance;
        }

        throw new Exception("The service '{$name}' is not registered in the container. Please ensure the service is correctly bound.");
    }
}
