<?php

namespace Potager\Router;

class Request
{
	protected ?Route $route;
	protected string $method;
	protected string $url;
	protected array $qs = [];
	protected array $params = [];
	protected array $body = [];
	protected array $accepts = [];

	public function __construct()
	{
		$this->method = $_SERVER['REQUEST_METHOD']; // 👈 Store the method used by the request
		$this->url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // 👈 Store the URL of the request
		$this->qs = $_GET; // 👈 Store the query string
		$this->body = $_POST; // 👈 Store the body of the request
		$accept = $_SERVER['HTTP_ACCEPT'] ?? 'text/html'; // 👈 Get the accept header
		$this->accepts = explode(',', $accept); // 👈 Explode and store the accepts types
	}

	/**
	 * Function to attach a route to the Request when resolved by the router
	 * @param Route $route The route resolved
	 * @return void
	 * @internal
	 */
	public function attachRoute(Route $route)
	{
		$this->route = $route;
		$this->params = $route->getParams(); // 👈 Store the parameters from the route
	}

	/**
	 * Returns the method (GET, POST, PUT, DELETE, etc.)
	 * @return string
	 */
	public function method(): string
	{
		return $this->method;
	}

	/**
	 * Returns the url
	 * @return string
	 */
	public function url(): string
	{
		return $this->url;
	}

	/**
	 * Returns the query string
	 * @return array
	 */
	public function qs()
	{
		return $this->qs;
	}

	/**
	 * Returns the route parameters
	 * @return array
	 */
	public function params()
	{
		return $this->params;
	}

	/**
	 * Returns the requests body
	 * @return array
	 */
	public function body()
	{
		return $this->body;
	}

	/**
	 * Merge the body and query string into a single array.
	 * Query string values will override body values if they have the same key
	 * @return array
	 */
	public function all()
	{
		return array_merge($this->body, $this->qs);
	}

	/**
	 * Returns only the specified keys from body and query string.
	 * @param array $keys The keys to return
	 * @return array
	 */
	public function only(array $keys)
	{
		return array_filter($this->all(), fn($key) => in_array($key, $keys), ARRAY_FILTER_USE_KEY);
	}

	/**
	 * Returns all keys except the specified ones from body and query string.
	 * @param array $keys The keys to exclude
	 * @return array
	 */
	public function except(array $keys)
	{
		return array_filter($this->all(), fn($key) => !in_array($key, $keys), ARRAY_FILTER_USE_KEY);
	}

	/**
	 * Return true if the request accepts the specified type.
	 * @param string $type
	 * @return bool
	 */
	public function accept(string $type): bool
	{
		return in_array($type, $this->accepts);
	}

	/**
	 * Negociate the best type from the list of types.
	 * @param array $types The types to negociate
	 * @return string|null The best type or null if none is found
	 */
	public function negociate(array $types): string|null
	{
		foreach ($this->accepts as $accept) {
			if (in_array($accept, $types)) {
				return $accept;
			}
		}
		return null;
	}

	/**
	 * Returns the list of accepted types.
	 * @return array
	 */
	public function accpets(): array
	{
		return $this->accepts;
	}
}