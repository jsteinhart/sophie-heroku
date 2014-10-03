<?php
class Symbic_Loader_AliasMap extends Symbic_Base_AliasMap implements Zend_Loader_PluginLoader_Interface
{
	public function getPaths()
	{
		return null;
	}

    public function addPrefixPath($prefix, $path)
	{
		//trigger_error('addPrefixPath is not supported for the AliasMap Loader');
		return $this;
	}

    public function removePrefixPath($prefix, $path = null)
	{
		//trigger_error('removePrefixPath is not supported for the AliasMap Loader');
		return $this;
	}

    public function isLoaded($name)
	{
		return true;
	}

    public function getClassName($name)
	{
		return $this->map($name);
	}

    public function load($name)
	{
		return $this->map($name);
	}
}