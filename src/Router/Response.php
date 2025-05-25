<?php

namespace Potager\Router;

class Response
{
	protected Router $router;
	protected $status;
	protected $body;
	protected $headers = [];
	protected $redirect;

	public function __construct(Router $router)
	{
		$this->router = $router;
	}

	public function status($code)
	{
		$this->status = $code;
		return $this;
	}

	public function safeStauts($code)
	{
		if (!isset($this->status)) {
			$this->status = $code;
		}
		return $this;
	}

	public function header(string $name, string $value)
	{
		$this->headers[$name] = $value;
		return $this;
	}

	public function send(mixed $body)
	{
		$this->body = $body;
		return $this;
	}

	public function ok(mixed $body)
	{
		$this->status = 200;
		$this->body = $body;
		return $this;
	}

	public function redirect(?string $path = null)
	{
		$redirect = new Redirect($this->router);
		$this->redirect = $redirect;
		if ($path)
			$this->redirect->toPath($path);
		return $this->redirect;
	}

	/**
	 * @internal This method is intended for internal use only.
	 */
	public function getStatus()
	{
		return $this->status ?? 200;
	}

	/**
	 * @internal This method is intended for internal use only.
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * @internal This method is intended for internal use only.
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * @internal This method is intended for internal use only.
	 */
	public function getRedirect()
	{
		return $this->redirect;
	}
}
