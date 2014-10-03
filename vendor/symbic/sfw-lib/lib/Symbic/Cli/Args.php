<?php

/**
 * Provides a structured interface to CLI parameters passed to a PHP Script
 */
class Symbic_Cli_Args
{

	private $parameters	 = array();
	private $shortOptions	 = array();
	private $longOptions	 = array();

	/**
	 * Parse CLI arguments and return Args Object or return false if no CLI interface is available
	 *
	 * @return boolean
	 */
	public function __construct()
	{
		if (PHP_SAPI != "cli")
		{
			return false;
		}

		for ($argNum = 2; $argNum <= $_SERVER['argc']; $argNum++)
		{
			$arg = $_SERVER['argv'][$argNum - 1];

			// handle long options
			if (substr($arg, 0, 2) == '--')
			{
				$option		 = substr($arg, 2);
				$hasValue	 = strpos($arg, '=');
				if ($hasValue === false)
				{
					$optionName	 = $option;
					$optionValue	 = true;
				}
				else
				{
					list($optionName, $optionValue) = explode('=', $option, 2);
				}
				$this->longOptions[$optionName] = $optionValue;
			}

			// handle short options
			elseif (substr($arg, 0, 1) == '-')
			{
				$option		 = substr($arg, 1);
				$hasValue	 = strpos($arg, '=');
				if ($hasValue === false)
				{
					$options = str_split($option);
					foreach ($options as $optionName)
					{
						$optionValue			 = true;
						$this->shortOptions[$optionName] = $optionValue;
					}
				}
				else
				{
					list($optionName, $optionValue) = explode('=', $option, 2);
					$this->shortOptions[$optionName] = $optionValue;
				}
			}
			else
			{
				$this->parameters[] = $arg;
			}
		}
		return true;
	}

	/**
	 * Get parameter array
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->parameters;
	}

	/**
	 * Get parameter by position or false if not set
	 *
	 * @param type $position
	 * @return string|false
	 */
	public function getParam($position)
	{
		$position--;
		if (isset($this->parameters[$position]))
		{
			return $this->parameters[$position];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get number of parameters
	 *
	 * @return int
	 */
	public function getParamCount()
	{
		return sizeof($this->parameters);
	}

	/**
	 * Get array of short options
	 *
	 * @return array
	 */
	public function getShortOptions()
	{
		return $this->shortOptions;
	}

	/**
	 * Get a short option by option name or false if not set
	 *
	 * @param type $name
	 * @return string|false
	 */
	public function getShortOption($name)
	{
		if (isset($this->shortOptions[$name]))
		{
			return $this->shortOptions[$name];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get number of short options
	 *
	 * @return int
	 */
	public function getShortOptionCount()
	{
		return sizeof($this->shortOptions);
	}

	/**
	 * Get array of long options
	 *
	 * @return array
	 */
	public function getLongOptions()
	{
		return $this->longOptions;
	}

	/**
	 * Get a long option by option name or false if not set
	 *
	 * @param type $name
	 * @return string|false
	 */
	public function getLongOption($name)
	{
		if (isset($this->longOptions[$name]))
		{
			return $this->longOptions[$name];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get number of long options
	 *
	 * @return int
	 */
	public function getLongOptionCount()
	{
		return sizeof($this->longOptions);
	}

}
