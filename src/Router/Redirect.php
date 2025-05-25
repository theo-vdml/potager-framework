<?php

namespace Potager\Router;

class Redirect
{
	protected Router $router;
	protected string|null $path;
	protected string|null $qs = null;

	public function __construct(Router $router)
	{
		$this->router = $router;
	}

	/**
	 * Redirect to a specific URL
	 * @param string $url The URL to redirect to
	 * @return Redirect
	 */
	public function toPath(string $path): static
	{
		$this->path = $path;
		return $this;
	}

	/**
	 * Redirect to a named route
	 * @param string $name The name of the route
	 * @throws \Exception If the route is not found
	 * @return Redirect
	 */
	public function toRoute(string $name): static
	{
		$route = $this->router->findByName($name);
		$this->path = $route->getPath();
		return $this;
	}

	/**
	 * Redirect to the previous URL (HTTP_REFERER)
	 * @param string $fallback The fallback URL if HTTP_REFERER is not set (default: '/')
	 * @return Redirect
	 */
	public function back(string $fallback = '/'): static
	{
		$previousUrl = $_SERVER['HTTP_REFERER'] ?? $fallback;
		$this->path = $previousUrl;
		return $this;
	}

	/**
	 * Add query string parameters to the redirect URL
	 * @param string|array $queryString The query string or an associative array of parameters or a string (without the leading '?')
	 * @return Redirect
	 */
	public function withQueryString(string|array $queryString)
	{
		if (is_array($queryString)) {
			$queryString = http_build_query($queryString);
		}
		$this->qs = "?{$queryString}";
		return $this;
	}

	/**
	 * Get the full redirect URL with query string
	 * @throws \Exception If the path is not set
	 * @return string The full URL to redirect to
	 * @internal
	 */
	public function getPath(): string
	{
		if ($this->path === null) {
			throw new \Exception('Redirect path is not set.');
		}
		$path = $this->path;
		$path = $this->qs ? "{$path}{$this->qs}" : $path;
		return $path;
	}

}