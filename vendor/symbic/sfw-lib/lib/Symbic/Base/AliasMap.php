<?php
class Symbic_Base_AliasMap extends Symbic_Base_AbstractSingleton
{
	/* null|"strtolower"|"ucfirst" */
	
	protected $_filter = null;
	protected $_map = array();
	protected $_returnOriginal = true;
	
	protected function filterAlias($alias)
	{
		if ($this->_filter === 'ucfirst')
		{
			return ucfirst($alias);
		}
		
		if ($this->_filter === 'strtolower')
		{
			return strtolower($alias);
		}
		
		throw new Exception('Unknown alias filter: ' . $this->_filter);
	}
	
	public function map($alias)
	{
		if (!is_null($this->_filter))
		{
			$alias = $this->filterAlias($alias);
		}

		if (!isset($this->_map[$alias]))
		{
			if ($this->_returnOriginal)
			{
				return $alias;
			}
			else
			{
				return null;
			}
		}
		return $this->_map[$alias];
	}

	public function getMap()
	{
		return $this->_map;
	}

	public function setMap($alias, $class)
	{
		if (!is_null($this->_filter))
		{
			$alias = $this->filterAlias($alias);
		}

		if (is_null($class))
		{
			if (isset($this->_map[$alias]))
			{
				unset($this->_map[$alias]);
			}
			return;
		}
		$this->_map[$alias] = $class;
	}

	public function unsetMap($alias)
	{
		if (!is_null($this->_filter))
		{
			$alias = $this->filterAlias($alias);
		}

		if (isset($this->_map[$alias]))
		{
			unset($this->_map[$alias]);
		}
	}
}