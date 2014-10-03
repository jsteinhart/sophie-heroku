<?php
class Symbic_Validate_PHPCode extends \Symbic_Validate_PHPSyntax
{
	const DISALLOWED = 'disallowed';

	protected $allowedFunctionsWhite = array();
	protected $allowedFunctionsWhiteAndBlack = array();

	protected $_messageTemplates = array (
		self :: SYNTAX => 'Syntax error',
		self :: DISALLOWED => 'Disallowed code'
	);

	public function setAllowedFunctionsWhite(array $allowedFunctionsWhite)
	{
		$this->allowedFunctionsWhite = $allowedFunctionsWhite;
	}

	public function getAllowedFunctionsWhite()
	{
		return $this->allowedFunctionsWhite;
	}

	public function setAllowedFunctionsWhiteAndBlack(array $allowedFunctionsWhiteAndBlack)
	{
		$this->allowedFunctionsWhiteAndBlack = $allowedFunctionsWhiteAndBlack;
	}

	public function getAllowedFunctionsWhiteAndBlack()
	{
		return $this->allowedFunctionsWhiteAndBlack;
	}

	public function isValid($value, $checkBlacklist = true)
	{
		if ($this->validateSyntax($value) === false)
		{
			return false;
		}
		
		// get allowed functions from cache
		if ($checkBlacklist)
		{
			$allowedFunctions = $this->getAllowedFunctionsWhiteAndBlack();
		}
		else
		{
			$allowedFunctions = $this->getAllowedFunctionsWhite();
		}

		// parse list of used calls
		$auditor = new Symbic_Php_Auditor($value);
		$callList = $auditor->getFunctionAndMethodCalls();

		$result = true;
		foreach ($callList as $stmt)
		{
			switch ($stmt['type'])
			{
				case 'function_call':

					if (isset($allowedFunctions[$stmt['name']]))
					{
						if($allowedFunctions[$stmt['name']] == 0)
						{
							$this->_messages[] = 'Disallowed code: Calling the function "' . $stmt['name'] . '" is discouraged. (Line ' . $stmt['line'] . ')';
							if (!in_array($this->_errors, self :: DISALLOWED))
							{
								$this->_errors[] = self :: DISALLOWED;
							}
							$result = false;
						}
					}
					else
					{
						$this->_messages[] = 'Disallowed code: The function "' . $stmt['name'] . '" is not explicitly allowed. (Line ' . $stmt['line'] . ')';
						if (!in_array($this->_errors, self :: DISALLOWED))
						{
							$this->_errors[] = self :: DISALLOWED;
						}
						$result = false;
					}

				break;

				case 'method_call':
				break;

				case 'static_call':
				break;

				case 'new':
					$this->_messages[] = 'Disallowed code: Instantiating a class is not allowed. (Line ' . $stmt['line'] . ')';
					if (!is_array($this->_errors, self :: DISALLOWED))
					{
						$this->_errors[] = self :: DISALLOWED;
					}
					$result = false;
				break;

				case 'global':
				case 'class':
				case 'propertyFetch':
				case 'staticPropertyFetch':
					$this->_messages[] = 'Disallowed code: "' . $stmt['type'] . '" (Line ' . $stmt['line'] . ')';
					$this->_error(self :: DISALLOWED);
					$result = false;
				break;

				case 'closure':
				case 'closureUse':
					$this->_messages[] = 'Using closures is discouraged. (Line ' . $stmt['line'] . ')';
					if (!is_array($this->_errors, self :: DISALLOWED))
					{
						$this->_errors[] = self :: DISALLOWED;
					}
					$result = false;
				break;

				// nodes which are okay:
				case 'return':
				break;

				default:
					$this->_messages[] = 'Disallowed code: Unknown code type: "' . $stmt['type'] . '". (Line ' . $stmt['line'] . ')';
					if (!is_array($this->_errors, self :: DISALLOWED))
					{
						$this->_errors[] = self :: DISALLOWED;
					}
					$result = false;
			}
		}
		return $result;
	}
}