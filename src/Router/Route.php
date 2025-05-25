<?php
namespace Potager\Router;

require_once __DIR__ . '/../Helpers/getMiddlewareByName.php';

class Route
{
	protected $name;
	protected $method;
	protected $path;
	protected $action;
	protected $params = [];
	protected $middlewares = [];

	public function __construct($method, $path, $action)
	{
		$this->method = $method;
		$this->path = $path;
		$this->action = $action;
	}

	public function name(string $name)
	{
		$this->name = $name;
		return $this;
	}

	public function method(string $method)
	{
		$this->method = $method;
		return $this;
	}

	public function path(string $path)
	{
		$this->path = $path;
		return $this;
	}

	public function action(string $action)
	{
		$this->action = $action;
		return $this;
	}

	public function match(string $method, string $uri)
	{

		$pattern = preg_replace('#:([\w]+)#', '(?P<\1>[^/]+)', $this->path);
		$pattern = "@^{$pattern}$@";

		if ($this->method == $method && preg_match($pattern, $uri, $matches)) {
			$params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

			$this->params = $params;

			return true;
		}

		return false;
	}

	public function getMethod()
	{
		return $this->method;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getAction(): mixed
	{
		return $this->action;
	}

	public function getParams(): array
	{
		return $this->params;
	}

	public function use(mixed $middlewares)
	{
		if (!is_array($middlewares)) {
			$middlewares = [$middlewares];
		}
		foreach ($middlewares as $middleware) {
			if (!is_string($middleware) && !is_callable($middleware)) {
				throw new \Exception("Middleware name must be a string or a callable");
			}
			$this->middlewares[] = $middleware;
		}
		return $this;
	}

	private function checkMiddlewareFunction(callable $middlewareFunction)
	{
		// Reflect the middleware function to inspect its parameters
		$reflection = new \ReflectionFunction($middlewareFunction);
		$parameters = $reflection->getParameters();

		// Ensure there is exactly one parameter
		if (count($parameters) !== 1) {
			throw new \Exception("Middleware function must accept exactly one argument.");
		}

		// Get the type of the first parameter
		$firstParameter = $parameters[0];
		$firstParameterType = $firstParameter->getType();

		// Check if the parameter type is HttpContext
		if (!$firstParameterType instanceof \ReflectionNamedType || $firstParameterType->getName() !== HttpContext::class) {
			throw new \Exception("Middleware function must accept exactly one argument of type " . HttpContext::class);
		}
	}

	public function getMiddlewares()
	{
		$middlewares = [];
		foreach ($this->middlewares as $middleware) {
			if (is_string($middleware)) {
				$middlewareFunction = getMiddlewareByName($middleware);
			} else {
				$middlewareFunction = $middleware;
			}
			$this->checkMiddlewareFunction($middlewareFunction);
			$middlewares[] = $middlewareFunction;

		}
		return $middlewares;
	}
}