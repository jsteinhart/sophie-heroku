<?php
class Symbic_Validate_PHPSyntax extends \Zend_Validate_Abstract
{
	const SYNTAX = 'syntax';
	
	protected $_messageTemplates = array (
		self :: SYNTAX => 'Syntax error',
	);

	public function validateSyntax($value)
	{
		$syntaxCheck = Symbic_Php_Syntaxcheck::validate($value);

		if ($syntaxCheck === true)
		{
			return true;
		}

		if ($syntaxCheck === false)
		{
			$this->_messageTemplates[self :: SYNTAX] = 'Unknown syntax error';
			$this->setMessages($this->_messageTemplates);
			$this->_error(self :: SYNTAX);
			return false;
		}

		if (!is_array($syntaxCheck))
		{
			throw new Exception('Syntax check did not return boolean or array with last error');
		}
		
		$errorReporting = error_reporting();
		$displayErrors = ini_get('display_errors');
		
		error_reporting(0);
		ini_set('display_errors', 0);

		$orgErrorHandler = set_error_handler(array($this, 'nullErrorHandler'), -1);
		trigger_error('cleared syntax check', E_USER_NOTICE);
		set_error_handler($orgErrorHandler);

		error_reporting($errorReporting);
		ini_set('display_errors', $displayErrors);

		$this->_messageTemplates[self :: SYNTAX] = 'Syntax error: ' . $syntaxCheck['message'] . ' in line ' . $syntaxCheck['line'];
		$this->setMessages($this->_messageTemplates);
		$this->_error(self :: SYNTAX);
		return false;
	}
	
	public function isValid($value)
	{
		return $this->validateSyntax($value);
	}
}