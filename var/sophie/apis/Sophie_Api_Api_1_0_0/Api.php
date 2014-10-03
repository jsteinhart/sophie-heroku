<?php
/**
 * SoPHIE Api API Class
 *
 * The API provides dynamic initialization of api classes
 */
class Sophie_Api_Api_1_0_0_Api extends Sophie_Api_Abstract
{
	/**
	 * Initialize and get an API object.
	 *
	 * @param String $name Name of the api to initialize
	 * @param String $version Version of the api to initialize
	 * @param Boolean $reset Reinitialize api object
	 * @return Sophie_Api_Abstract
	 */
	public function get($name, $version = null, $reset = false)
	{
		if (!preg_match('/^[a-zA-Z]+$/', $name) && !preg_match('/^([a-zA-Z]+)_([a-zA-Z]+)$/', $name))
		{
			throw new Exception('Illegal api name ' . $name);
		}
		
		// TODO: validate version
		// TODO: implement versioning
		
		return $this->getContext()->getApi($name, $version, $reset);
		
	}
}