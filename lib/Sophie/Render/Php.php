<?php
class Sophie_Render_Php extends Zend_View
{
	private $_viewScriptString = '';

	private $context = null;
	private $steptype = null;
	private $localVars = array ();

	public function setContext($context)
	{
		$this->context = $context;
	}

	public function getContext()
	{
		return $this->context;
	}

	public function setSteptype($steptype)
	{
		$this->steptype = $steptype;
	}

	public function setLocalVar($name, $value)
	{
		$this->localVars[$name] = $value;
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
		unset ($this->localVars[$name]);
	}

	public function clearLocalVars()
	{
		$this->localVars = array ();
	}

	public function render($viewScriptString)
	{
		if ($this->useStreamWrapper())
		{
			$viewScriptString = preg_replace('/\<\?\=/', "<?php echo ", $viewScriptString);
			$viewScriptString = preg_replace('/<\?(?!xml|php)/s', '<?php ', $viewScriptString);
		}

		// check syntax and return unrendered content on error
		$scriptMd5 = md5($viewScriptString);
		$cacheName = 'sophieCodeSanitizerCache_' . $scriptMd5;

		$cache = Zend_Registry :: get('Zend_Cache');
		$sanitizerCheck = $cache->load($cacheName);

		if ($sanitizerCheck !== true)
		{
			$sanitizerCheck = true;
			try
			{
				$sanitizer = new Sophie_Validate_PHPCode();
				$sanitizerCheck = $sanitizer->isValid($viewScriptString, false /* check whitelist only */);
			}
			catch (Exception $e)
			{
				$sanitizerCheck = false;
			}
			if (!$sanitizerCheck)
			{
				Sophie_Db_Session_Log :: log($this->context->getSessionId(), 'Sanitizer error in render execution', 'error', implode("\n", $sanitizer->getMessages()));
			}
			$cache->save($sanitizerCheck, $cacheName);
		}

		if (!$sanitizerCheck)
		{
			return '<h1>Error</h1>See session log!' . "\n<h2>Code</h2><pre>" . $this->escape($viewScriptString) . '</pre>';
		}

		$this->_viewScriptString = $viewScriptString;
		unset ($viewScriptString); // remove $viewScriptString from local scope

		return $this->_run();
	}

	protected function _run()
	{
		if (is_array($this->localVars))
		{
			extract($this->localVars);
		}

		// evaluate script
		ob_start();
		eval ('?>' . $this->_viewScriptString);

		// return content
		return ob_get_clean();
	}
}