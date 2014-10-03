<?php
/**
 *
 */
trait Symbic_Base_SingletonTrait
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
	final public static function getInstance()
	{
		static $instances	 = array();
		$instanceName		 = get_called_class();

		if (!isset($instances[$instanceName]))
		{
			$instances[$instanceName] = new $instanceName();
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