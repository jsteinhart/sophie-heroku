<?php
class Sophie_Validate_AbstractLabel extends Zend_Validate_Abstract
{
	const MSG_INVALID_CHAR = 'msgInvalidChar';
	const MSG_LENGTH = 'msgLength';
	const MSG_EMPTY = 'msgEmpty';
	const MSG_NOT_UNIQUE = 'msgNotUnique';
	const MSG_NOT_EXISTS = 'msgNotExists';
	const MSG_EXISTS = 'msgExists';

	protected $_emptyCheck = true;
	
	protected $_maxLengthCheck = true;
	protected $_maxLength = 255;
	
	protected $_patternCheck = true;
	protected $_pattern = '/[^a-z0-9_]/i';

	protected $_uniqueCheck = false;

	protected $_existsCheck = false;
	protected $_notExistsCheck = false;

	protected $_messageVariables = array('maxLength' => '_maxLength');

	protected $_messageTemplates = array(
		self::MSG_INVALID_CHAR => "'%value%' is not valid. Please use Alphabet, Numbers and _ only.",
		self::MSG_LENGTH => "'%value%' exceeds the maximum length of %maxLength% characters.",
		self::MSG_EMPTY => "The value must not be empty.",
		self::MSG_NOT_UNIQUE => "'%value%' is not a unique label.",
		self::MSG_NOT_EXISTS => "'%value%' does not exist.",
		self::MSG_EXISTS => "'%value%' already exists.",
	);

    public function __construct($optionsOrEmptyCheck = null, $maxLengthCheck = null, $maxLength = null, $patternCheck = null, $pattern = null, $uniqueCheck = null, $existsCheck = null, $notExistsCheck = null)
    {
		$emptyCheck = true;

		if ($optionsOrEmptyCheck instanceof Zend_Config) {
			$optionsOrEmptyCheck = $optionsOrEmptyCheck->toArray();
		}

		if (is_array($optionsOrEmptyCheck))
		{
			if (array_key_exists('emptyCheck', $optionsOrEmptyCheck))
			{
				$emptyCheck = $optionsOrEmptyCheck['emptyCheck'];
			}

			if (array_key_exists('maxLengthCheck', $optionsOrEmptyCheck))
			{
				$maxLengthCheck = $optionsOrEmptyCheck['maxLengthCheck'];
			}

			if (array_key_exists('maxLength', $optionsOrEmptyCheck))
			{
				$maxLength = $optionsOrEmptyCheck['maxLength'];
			}

			if (array_key_exists('patternCheck', $optionsOrEmptyCheck))
			{
				$patternCheck = $optionsOrEmptyCheck['patternCheck'];
			}
			
			if (array_key_exists('pattern', $optionsOrEmptyCheck))
			{
				$pattern = $optionsOrEmptyCheck['pattern'];
			}

			if (array_key_exists('uniqueCheck', $optionsOrEmptyCheck))
			{
				$uniqueCheck = $optionsOrEmptyCheck['uniqueCheck'];
			}

			if (array_key_exists('existsCheck', $optionsOrEmptyCheck))
			{
				$existsCheck = $optionsOrEmptyCheck['existsCheck'];
			}

			if (array_key_exists('notExistsCheck', $optionsOrEmptyCheck))
			{
				$notExistsCheck = $optionsOrEmptyCheck['notExistsCheck'];
			}
		}
		else
		{
			$emptyCheck = $optionsOrEmptyCheck;
		}

		if (!is_null($emptyCheck))
		{
			$this->setEmptyCheck($emptyCheck);
		}

		if (!is_null($maxLengthCheck))
		{
			$this->setMaxLengthCheck($maxLengthCheck);
		}

		if (!is_null($maxLength))
		{
			$this->setMaxLength($maxLength);
		}

		if (!is_null($patternCheck))
		{
			$this->setPatternCheck($patternCheck);
		}

		if (!is_null($pattern))
		{
			$this->setPattern($pattern);
		}

		if (!is_null($uniqueCheck))
		{
			$this->setUniqueCheck($uniqueCheck);
		}

		if (!is_null($existsCheck))
		{
			$this->setExistsCheck($existsCheck);
		}

		if (!is_null($notExistsCheck))
		{
			$this->setNotExistsCheck($notExistsCheck);
		}
		
		$this->init();
	}

	protected function init()
	{
	}
	
	public function setEmptyCheck($emptyCheck)
	{
		$this->_emptyCheck = $emptyCheck;
	}

	public function getEmptyCheck()
	{
		return $this->_emptyCheck;
	}

	public function setMaxLengthCheck($maxLengthCheck)
	{
		$this->_maxLengthCheck = $maxLengthCheck;
	}

	public function getMaxLengthCheck()
	{
		return $this->_maxLengthCheck;
	}

	public function setMaxLength($maxLangth)
	{
		$this->_maxLangth = $maxLangth;
	}

	public function getMaxLength()
	{
		return $this->_maxLangth;
	}

	public function setPatternCheck($patternCheck)
	{
		$this->_patternCheck = $patternCheck;
	}

	public function getPatternCheck()
	{
		return $this->_patternCheck;
	}
	
	public function setPattern($pattern)
	{
		$this->_pattern = $pattern;
	}

	public function getPattern()
	{
		return $this->_pattern;
	}

	public function setUniqueCheck($uniqueCheck)
	{
		$this->_uniqueCheck = $uniqueCheck;
	}

	public function getUniqueCheck()
	{
		return $this->_uniqueCheck;
	}

	public function setExistsCheck($existsCheck)
	{
		$this->_existsCheck = $existsCheck;
	}

	public function getExistsCheck()
	{
		return $this->_existsCheck;
	}

	public function setNotExistsCheck($notExistsCheck)
	{
		$this->_notExistsCheck = $notExistsCheck;
	}

	public function getNotExistsCheck()
	{
		return $this->_notExistsCheck;
	}
	///////////////////////////////////////////////////

	public function uniqueCheck($value)
	{
		throw Exception('Checking for Uniqueness is not implemented for this Validator');
	}

	public function existsCheck($value)
	{
		throw Exception('Checking for Existence is not implemented for this Validator');
	}
	
	public function isValid($value)
	{
		$this->_setValue($value);

		if ($this->getEmptyCheck() && $value === '')
		{
			$this->_error(self::MSG_EMPTY);
			return false;
		}

		$result = true;

		if ($this->getMaxLengthCheck() && strlen($value) > $this->_maxLength)
		{
			$this->_error(self::MSG_LENGTH);
			$result = false;
		}

		if ($this->getPatternCheck() && preg_match($this->getPattern(), $value))
		{
			$this->_error(self::MSG_INVALID_CHAR);
			$result = false;
		}

		if ($result && $this->getUniqueCheck())
		{
			$result = $this->uniqueCheck($value);
		}

		if ($result && $this->getExistsCheck())
		{
			$result = $this->existsCheck($value);
		}

		if ($result && $this->getNotExistsCheck())
		{
			$result = $this->notExistsCheck($value);
		}

		return $result;
	}
}