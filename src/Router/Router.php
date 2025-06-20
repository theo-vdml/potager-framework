<?php

namespace Potager\Router;

use Potager\App;
use Potager\Container\Container;
use Potager\Exceptions\HttpException;
use Potager\View;
use Exception;
use Throwable;

class Router
{

	// Array to hold all the routes
	protected array $routes = [];

	protected Container $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * =================================
	 * 	Methods to register new routes
	 * =================================
	 */


	// Add a GET route
	public function get($path, $controllerAction)
	{
		return $this->register('GET', $path, $controllerAction);
	}

	// Add a POST route
	public function post($path, $controllerAction)
	{
		return $this->register('POST', $path, $controllerAction);
	}

	// Add a PUT route
	public function put($path, $controllerAction)
	{
		return $this->register('PUT', $path, $controllerAction);
	}

	// Add a PATCH route
	public function patch($path, $controllerAction)
	{
		return $this->register('PATCH', $path, $controllerAction);
	}

	// Add a DELETE route
	public function delete($path, $controllerAction)
	{
		return $this->register('DELETE', $path, $controllerAction);
	}


	/**
	 * =============================================
	 * 	Unified helper to handle route registration
	 * =============================================
	 */

	protected function register($method, $path, $controllerAction)
	{
		$route = new Route($method, $path, $controllerAction);
		$this->routes[] = $route;
		return $route;
	}

	/**
	 * =============================
	 * 	Methods to retrieve routes
	 * =============================
	 */

	public function findByName($name)
	{
		foreach ($this->routes as $route) {
			if ($route->getName() === $name) {
				return $route;
			}
		}
		throw new Exception("Route named {$name} does not exist");
	}

	/**
	 * =========================================================
	 * 	Method to dispatch the request to the appropriate route
	 * =========================================================
	 */

	public function handleRequest()
	{
		$context = $this->container->make(HttpContext::class);
		$request = $context->request();

		$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$method = $_SERVER['REQUEST_METHOD'];
		foreach ($this->routes as $route) {
			if ($route->match($method, $uri)) {
				$request->attachRoute($route);
				$this->invokeController($route, $context);
			}
		}
		throw new HttpException(404);

	}

	protected function invokeController(Route $route, HttpContext $context)
	{
		$middlewares = $route->getMiddlewares();

		$output = null;

		$controller = function () use ($route, $context, &$output): void {
			[$controller, $action] = $route->getAction();
			$output = (new $controller())->$action($context);
		};

		$pipeline = array_reverse($middlewares);
		$next = $controller;
		foreach ($pipeline as $middleware) {
			$prev = $next;
			$next = fn(): mixed => $middleware($context, $prev);
		}

		$next();
		$response = $this->resolveControllerOutput($output, $context->response());
		$this->sendResponse($response);
		exit;
	}

	protected function resolveControllerOutput(mixed $controllerResult, Response $response): Response
	{
		// If the controller returned a instance of Reponse, it should prior to the default $reponse object
		if ($controllerResult instanceof Response) {
			$response = $controllerResult;
		}
		// If the controller returned a instance of a View, it should prior to any other values
		elseif ($controllerResult instanceof View) {
			$controllerResult->with("auth", App::useAuth());
			$response->send($controllerResult->render());
		}
		// If the controller returned a raw value, it should be used as the body of the response
		elseif (isset($controllerResult)) {
			$response->send($controllerResult);
		}
		return $response;
	}

	protected function sendResponse(Response $response): void
	{
		// Apply the redirection if any was set
		if ($response->getRedirect() instanceof Redirect) {
			$path = $response->getRedirect()->getPath();
			header("Location: $path", true, 302);
			exit; // Stop the script after redirection
		}

		// Set the headers according to the response object
		$headers = $response->getHeaders();
		foreach ($headers as $name => $value) {
			header("$name: $value");
		}

		// Set the status code
		http_response_code($response->getStatus());

		// return the response body
		echo $response->getBody();
	}
}
