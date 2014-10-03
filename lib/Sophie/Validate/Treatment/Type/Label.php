<?php
class Sophie_Validate_Treatment_Type_Label extends Sophie_Validate_AbstractLabel
{
	public $treatmentId = null;

	protected $_maxLength = 2;

	protected function init()
	{
		$this->_messageTemplates[self::MSG_NOT_UNIQUE] = "'%value%' is not a unique participant type label within the treatment.";
	}

	public function uniqueCheck($value)
	{
		if (is_null($this->treatmentId))
		{
			throw new Exception('TreatmentId needs to be passed to ' . __CLASS__ . ' for Uniqueness check.');
		}

		// validate uniqueness with Zend_Validate_Db_NoRecordExists:
		$excludeStatement = 'treatmentId = ' . (int)$this->treatmentId;

		$dbNoRecordExistsValidator = new Zend_Validate_Db_NoRecordExists(
			array(
				'table' => 'sophie_treatment_type',
				'field' => 'label',
				'exclude' => $excludeStatement
				)
		);

		if ($dbNoRecordExistsValidator->isValid($value))
		{
			return true;
		}

		$this->_error(self::MSG_NOT_UNIQUE);
		return false;
	}
}
