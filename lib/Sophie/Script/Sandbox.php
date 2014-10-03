<?php
class Sophie_Script_Sandbox
{
	private $_context = null;
	private $_localVars = array();
	//private $_preDefinedLocalVars = array();
	//private $_postDefinedLocalVars = array();
	private $_scriptString = '';
	private $_evalReturn = null;
	private $_evalOutput = null;
	private $_returnEvalOutput = false;
	private $_throwOriginalException = false;
	private $_logSanitizerException = true;
	private $_logEvalException = true;

	public function __construct()
	{
	}

	public function setContext($context)
	{
		$this->_context = $context;
	}

	public function getContext()
	{
		return $this->_context;
	}

	public function setThrowOriginalException($throwOriginalException)
	{
		$this->_throwOriginalException = $throwOriginalException;
	}

	public function getThrowOriginalException()
	{
		return $this->_throwOriginalException;
	}

	public function setLocalVar($name, $value)
	{
		$this->_localVars[$name] = $value;
	}

	public function setLocalVars($varList)
	{
		foreach ($varList as $name => $value)
		{
			$this->setLocalVar($name, $value);
		}
	}

	public function unsetLocalVar($name)
	{
		unset($this->_localVars[$name]);
	}

	public function clearLocalVars()
	{
		$this->_localVars = array ();
	}

	public function getEvalOutput()
	{
		return $this->_evalOutput;
	}

	public function clearEvalOutput()
	{
		return $this->_evalOutput;
	}

	public function run($scriptString, $defaultReturn = null, $runSanitizer = true)
	{
		if ($runSanitizer)
		{
			// check syntax and throw exception on error
			$scriptMd5 = md5($scriptString);
			$cacheName = 'sophieCodeSanitizerCache_' . $scriptMd5;

			$cache = Zend_Registry :: get('Zend_Cache');
			$sanitizerCheck = $cache->load($cacheName);

			if ($sanitizerCheck !== true)
			{
				$sanitizerCheck = true;
				try
				{
					$sanitizer = new Sophie_Validate_PHPCode();
					$sanitizerCheck = $sanitizer->isValid('<?php ' . $scriptString, false /* check whitelist only */);
					if (!$sanitizerCheck && $this->_logSanitizerException)
					{
						Sophie_Db_Session_Log :: log($this->getContext()->getSessionId(), 'Sanitizer check failed', 'error', implode("\n", $sanitizer->getMessages()));
						throw new Exception('Sanitizer check failed: ' . implode(", ", $sanitizer->getMessages()));
					}
					else
					{
						$cache->save($sanitizerCheck, $cacheName);
					}
				}
				catch (Exception $e)
				{
					Sophie_Db_Session_Log :: log($this->getContext()->getSessionId(), 'Sanitizer Exception', 'error', print_r($e, 1));
					throw new Sophie_Script_Sandbox_Exception('Sanitizer Exception', null, $e);
				}
			}
		}

		$this->_scriptString = $scriptString;
		$this->_defaultReturn = $defaultReturn;
		return $this->_run();
	}

	protected function _run()
	{
		if (is_array($this->_localVars))
		{
			extract($this->_localVars);
		}

		//$this->_preDefinedLocalVars = get_defined_vars();
		
		// evaluate script
		try
		{
			ob_start();
			$this->_evalReturn = eval($this->_scriptString);
			$this->_evalOutput = ob_get_clean();
		}
		catch(Exception $e)
		{
			if ($this->_throwOriginalException)
			{
				throw $e;
			}
			else
			{
				throw new Sophie_Script_Sandbox_Exception('Error in sandbox script execution', null, $e);
				//throw new Exception('Error in sandbox script execution: ');
			}
		}

		//$this->_postDefinedLocalVars = get_defined_vars();
		
		// return content
		if ($this->_returnEvalOutput)
		{
			return $this->_evalOutput;
		}
		else
		{
			return $this->_evalReturn;
		}
	}
}