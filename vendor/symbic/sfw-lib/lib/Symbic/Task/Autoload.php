<?php
/**
 *
 */
class Symbic_Task_Autoload
{

	/**
	 *
	 * @var array
	 */
	protected $autoloadNamespaces = array();

	public function __construct(&$options = array())
	{
		// set autoloadNamespaces from options
		if (isset($options['autoloadNamespaces']) && is_array($options['autoloadNamespaces']))
		{
			$this->autoloadNamespaces = array_replace($this->autoloadNamespaces, $options['autoloadNamespaces']);
			unset($options['autoloadNamespaces']);
		}

		// register autoloader
		spl_autoload_register(array($this, 'autoloadTaskClass'));
	}

	public function __deconstruct()
	{
		spl_autoload_unregister(array($this, 'autoloadTaskClass'));
	}

	/**
	 *
	 * @param string $className
	 */
	final public function autoloadTaskClass($className)
	{
		foreach ($this->autoloadNamespaces as $prefix => $path)
		{
			if (substr($className, 0, strlen($prefix)) == $prefix)
			{
				$classParts = explode('_', $className);
				array_shift($classParts);
				array_shift($classParts);
				$classFile =  $path . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $classParts) . '.php';
				if (file_exists($classFile))
				{
					include $classFile;
					return true;
				}
			}
		}
		throw new Exception('Could not load ' . $className);
	}

}
