<?php
// we do not want to use the zend view stream wrapper => inherit from Zend_View_Abstract instead of Zend_View
abstract class Symbic_View_AbstractView extends Zend_View_Abstract
{
	protected $_loaders = array();

	protected $_defaultHelperLoader			= NULL;
	protected $_defaultFilterLoader			= NULL;

	// extend to add symbic specific prefixes
	public function __construct($config = array())
	{
		// always use strictVars mode
		if (!isset($config['strictVars']))
		{
			$config['strictVars'] = true;
		}

		// deactivate parent directory traversal checks
		$config['lfiProtectionOn'] = false;

		parent::__construct($config);
	}

	public function getPluginLoader($type)
	{
		$type = strtolower($type);

		if (!array_key_exists($type, $this->_loaders))
		{
			if ($type === 'helper')
			{
				$loaderClass = $this->_defaultHelperLoader;
			}
			elseif ($type === 'filter')
			{
				$loaderClass = $this->_defaultFilterLoader;
			}
			else
			{
				$e = new Zend_View_Exception(sprintf('Invalid plugin loader type "%s"; cannot retrieve', $type));
				$e->setView($this);
				throw $e;
			}

			$this->_loaders[$type] = $loaderClass::getInstance();
		}

		return $this->_loaders[$type];
	}

	public function setPluginLoader(Zend_Loader_PluginLoader $loader, $type)
	{
		$type = strtolower($type);
		if ($type !== 'helper' && $type !== 'filter')
		{
			$e = new Zend_View_Exception(sprintf('Invalid plugin loader type "%s"', $type));
			$e->setView($this);
			throw $e;
		}

		$this->_loaders[$type] = $loader;
		return $this;
	}

	protected function _run()
	{
		// use require instead of include to throw fatal errors in view scripts
		require(func_get_arg(0));
	}
}