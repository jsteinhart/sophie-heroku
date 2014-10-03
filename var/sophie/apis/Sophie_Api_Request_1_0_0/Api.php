<?php
/**
 * SoPHIE Request API Class
 *
 * The Request API provides access to parameters passed with the current HTTP request.
 */
class Sophie_Api_Request_1_0_0_Api extends Sophie_Api_Abstract
{
	/**
	 * Check whether the HTTP requests has a value for the specified parameter.
	 *
     * @param String $parameterName
	 * @return Boolean True if the parameter extsis and has avalue other than null, false otherwise.
	 */
	public function hasParam($parameterName)
	{
		return null !== $this->getContext()->getController()->getRequest()->getParam($parameterName, null);
	}

	/**
	 * Get the value for the specified parameter.
	 *
     * @param String $parameterName
	 * @param String $defaultValue
	 * @return String Parameter value if is is set, the passed defaultValue otherwise.
	 */
	public function getParam($parameterName, $defaultValue = null)
	{
		return $this->getContext()->getController()->getRequest()->getParam($parameterName, $defaultValue);
	}

	/**
	 * Get all parameter values.
	 *
	 * @return Array Associative array of parameters names and values
	 */
	public function getParams()
	{
		return $this->getContext()->getController()->getRequest()->getParams();
	}

	/**
	 * Get the value for the specified header field.
	 *
     * @param String $headerName
	 * @return String|false Header value if is is set, false otherwise.
	 */
	public function getHeader($headerName)
	{
		return $this->getContext()->getController()->getRequest()->getHeader($headerName);
	}

	/**
	 * Get the request method.
	 *
	 * @return String Request method.
	 */
	public function getMethod($headerName)
	{
		return $this->getContext()->getController()->getRequest()->getMethod();
	}

	/**
	 * Check header for AJAX Request indication.
	 *
	 * @return Boolean True if the request is an ajax request, false otherwise.
	 */
	public function isXmlHttpRequest()
	{
		return $this->getContext()->getController()->getRequest()->isXmlHttpRequest();
	}

	/**
	 * Check request protocol for secure HTTPS connection.
	 *
	 * @return Boolean True if the request was made over a HTTPS connection, false otherwise.
	 */
	public function isSecure()
	{
		return $this->getContext()->getController()->getRequest()->isSecure();
	}

	/*
	TODO:
	Additional functions to be proxied
	isGet()
	isPost()
	isPut()
	isDelete()
	isHead()
	isOptions()
	getHttpHost() : string
	isFlashRequest() : boolean
	getPost(string $key = null, mixed $default = null) : mixed
	getServer(string $key = null, mixed $default = null) : mixed
	getScheme() : string
	getRequestUri() : string
	getRawBody() : string | false
	getQuery(string $key = null, mixed $default = null) : mixed
	getBaseUrl( $raw = false) : string
	getPathInfo() : string
	getClientIp(boolean $checkProxy = true) : string
	getCookie(string $key = null, mixed $default = null) : mixed
	getEnv(string $key = null, mixed $default = null) : mixed
	*/
}