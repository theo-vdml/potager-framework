<?php

namespace Potager\Router;

use Potager\App;
use Potager\Exceptions\HttpException;
use Potager\View;
use Exception;
use Throwable;

class Router
{
	// Array to hold all the routes
	protected $routes = [];

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
		$request = new Request();
		$response = new Response($this);
		$context = new HttpContext($request, $response);
		try {
			$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
			$method = $_SERVER['REQUEST_METHOD'];
			foreach ($this->routes as $route) {
				if ($route->match($method, $uri)) {
					$request->attachRoute($route);
					$this->invokeController($route, $context);
					exit;
				}
			}
			throw new HttpException(404);
		} catch (Throwable $e) {
			$this->exceptionHandler($e, $context);
		}
	}

	protected function invokeController(Route $route, HttpContext $context)
	{
		$middlewares = $route->getMiddlewares();

		$output = null;

		$controller = function () use ($route, $context, &$output) {
			[$controller, $action] = $route->getAction();
			$output = (new $controller())->$action($context);
		};

		$pipeline = array_reverse($middlewares);
		$next = $controller;
		foreach ($pipeline as $middleware) {
			$prev = $next;
			$next = fn() => $middleware($context, $prev);
		}

		$next();
		$response = $this->resolveControllerOutput($output, $context->response());
		$this->sendResponse($response);
		exit;
	}

	protected function resolveControllerOutput(mixed $controllerResult, Response $response)
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

	protected function exceptionHandler(Throwable $e, HttpContext $ctx)
	{
		if ($e instanceof HttpException) {
			$this->handleHttpException($e, $ctx);
		} else {
			$this->handleException($e, $ctx);
		}
	}

	protected function handleHttpException(HttpException $e, HttpContext $ctx)
	{
		$code = $e->getCode();
		$message = $e->getMessage();

		http_response_code($code);
		$best = $ctx->request()->negociate(['application/json', 'text/html']);
		switch ($best) {
			case 'application/json':
				header('Content-Type: application/json');
				$response = ['error' => true, 'message' => $message, 'status' => $code];
				echo json_encode($response);
				break;
			case 'text/html':
				header('Content-Type: text/html');
				$custom_error_file = __DIR__ . "/../../app/{$code}.php";
				if (file_exists($custom_error_file)) {
					include $custom_error_file;
				} else {
					include __DIR__ . '/default/http_exception.php';
				}
				break;
			default:
				header('Content-Type: text/plain');
				echo "Error {$code}: {$message}";
				break;
		}
		exit;
	}

	protected function handleException(Throwable $e, HttpContext $ctx)
	{
		$class = get_class($e);
		$message = $e->getMessage();
		$trace = $e->getTraceAsString();
		$file = $e->getFile();
		$line = $e->getLine();
		$excerpt = $this->getFileExcerpt($file, $line);

		http_response_code(500);
		$best = $ctx->request()->negociate(['application/json', 'text/html']);
		switch ($best) {
			case 'application/json':
				header('Content-Type: application/json');
				$response = ['error' => true, 'message' => $message, 'status' => 500, 'trace' => $trace, 'file' => $file, 'line' => $line];
				echo json_encode($response);
				break;
			case 'text/html':
				header('Content-Type: text/html');
				include __DIR__ . '/default/exception.php';
				break;
			default:
				header('Content-Type: text/plain');
				echo "Internal Server Error : {$message}";
				break;
		}
		exit;
	}

	protected function getFileExcerpt(string $file, int $line, int $padding = 10): array
	{
		if (!is_readable($file))
			return ['excerpt' => 'Cannot read source file.', 'start' => 0, 'highlight' => $line];

		$lines = file($file);
		$start = max(0, $line - $padding - 1);
		$end = min(count($lines), $line + $padding);

		$excerpt = array_slice($lines, $start, $end - $start, true);
		return [
			'excerpt' => array_map(fn($l) => htmlspecialchars($l), $excerpt),
			'start' => $start + 1,
			'highlight' => $line - $start,
		];
	}
}
