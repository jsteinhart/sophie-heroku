<?php

/**
 *
 */
abstract class Symbic_Base_AbstractNamedSingleton
{
	/**
	 *
	 */
	final protected function __construct()
	{
		$this->init();
	}

	/**
	 * Init method called on creation of singleton instance
	 */
	protected function init()
	{

	}

	/**
	 * Get the singleton instance of the class
	 *
	 * @staticvar array $instances
	 * @return object
	 */
	final public static function getNamedInstance($name)
	{
		static $instances	 = array();
		$instanceName		 = get_called_class();

		if (!isset($instances[$instanceName]))
		{
			$instances[$instanceName] = array();
		}
		
		if (!isset($instances[$instanceName][$name]))
		{
			$instances[$instanceName][$name] = new $instanceName();
		}

		return $instances[$instanceName][$name];
	}

		/**
	 * Get the singleton instance of the class
	 *
	 * @staticvar array $instances
	 * @return object
	 */
	final public static function getNamedInstances()
	{
		static $instances	 = array();
		$instanceName		 = get_called_class();

		if (!isset($instances[$instanceName]))
		{
			$instances[$instanceName] = array();
		}

		return $instances[$instanceName];
	}

	/**
	 * Prevent user from cloning a singleton
	 */
	final private function __clone()
	{

	}

}
