<?php
class Sophie_Validate_Treatment_Sessiontype_Name extends Sophie_Validate_AbstractLabel
{
	protected $_patternCheck = false;

	public $treatmentId = null;
	public $sessiontypeId = null;

	protected function init()
	{
		$this->_messageTemplates[self::MSG_NOT_UNIQUE] = "'%value%' is not a unique sessiontype name within the treatment.";
	}

	public function uniqueCheck($value)
	{
		if (is_null($this->treatmentId))
		{
			throw new Exception('TreatmentId needs to be passed to ' . __CLASS__ . ' for Uniqueness check.');
		}

		// validate uniqueness with Zend_Validate_Db_NoRecordExists:
		$excludeStatement = 'treatmentId = ' . (int)$this->treatmentId;

		if (!is_null($this->sessiontypeId))
		{
			$excludeStatement .= ' AND id != ' . (int)$this->sessiontypeId;
		}

		$dbNoRecordExistsValidator = new Zend_Validate_Db_NoRecordExists(
			array(
				'table' => 'sophie_treatment_sessiontype',
				'field' => 'name',
				'exclude' => $excludeStatement,
				)
		);

		if ($dbNoRecordExistsValidator->isValid($value) == false)
		{
			$this->_error(self::MSG_NOT_UNIQUE);
			return false;
		}

		return true;
	}
}