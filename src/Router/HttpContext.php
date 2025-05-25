<?php

namespace Potager\Router;

class HttpContext
{
	protected Request $request;
	protected Response $response;

	public function __construct(Request $request, Response $response)
	{
		$this->request = $request;
		$this->response = $response;
	}

	/**
	 * Returns the request object. Use this method to access request data like parameters, headers, etc.
	 * @return Request
	 */
	public function request()
	{
		return $this->request;
	}

	/**
	 * Returns the response object. Use this method to define the response.
	 * @return Response
	 */
	public function response()
	{
		return $this->response;
	}
}
