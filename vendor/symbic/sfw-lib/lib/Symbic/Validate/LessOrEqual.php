<?php
class Symbic_Validate_LessOrEqual extends Zend_Validate_LessThan
{
	const NOT_LESS_OR_EQUAL = 'notLessOrEqual';

	protected $_messageTemplates = array(
		self::NOT_LESS_OR_EQUAL => "'%value%' is not less than or equal '%max%'",
	);

	public function isValid($value)
	{
		$this->_setValue($value);

		if ($value > $this->_max)
		{
			$this->_error(self::NOT_LESS_OR_EQUAL);
			return false;
		}
		return true;
	}
}