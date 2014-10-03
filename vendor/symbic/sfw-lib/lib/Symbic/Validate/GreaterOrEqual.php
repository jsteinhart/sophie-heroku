<?php
class Symbic_Validate_GreaterOrEqual extends Zend_Validate_GreaterThan
{
	const NOT_GREATER_OR_EQUAL = 'notGreaterOrEqual';

	protected $_messageTemplates = array(
		self::NOT_GREATER_OR_EQUAL => "'%value%' is not greater than or equal '%min%'",
	);

	public function isValid($value)
	{
		$this->_setValue($value);

		if ($value < $this->_min)
		{
			$this->_error(self::NOT_GREATER_OR_EQUAL);
			return false;
		}
		return true;
	}
}