<?php
class Sophie_Validate_Treatment_Stepgroup_Label extends Sophie_Validate_AbstractLabel
{
	public $treatmentId = null;
	public $stepgroupId = null;

	protected $_maxLength = 36;

	protected function init()
	{
		$this->_messageTemplates[self::MSG_NOT_UNIQUE] = "'%value%' is not a unique stepgroup label within the treatment.";
	}

	public function uniqueCheck($value)
	{
		if (is_null($this->treatmentId))
		{
			throw new Exception('TreatmentId needs to be passed to ' . __CLASS__ . ' for Uniqueness check.');
		}

		// validate uniqueness with Zend_Validate_Db_NoRecordExists:
		$excludeStatement = 'treatmentId = ' . (int)$this->treatmentId;

		if (!is_null($this->stepgroupId))
		{
			$excludeStatement .= ' AND id != ' . (int)$this->stepgroupId;
		}

		$dbNoRecordExistsValidator = new Zend_Validate_Db_NoRecordExists(
			array(
				'table' => 'sophie_treatment_stepgroup',
				'field' => 'label',
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

	public function existsCheck($value)
	{
		if (is_null($this->treatmentId))
		{
			throw new Exception('TreatmentId needs to be passed to ' . __CLASS__ . ' for Existence check.');
		}

		// validate uniqueness with Zend_Validate_Db_NoRecordExists:
		$excludeStatement = 'treatmentId != ' . (int)$this->treatmentId;

		$dbNoRecordExistsValidator = new Zend_Validate_Db_NoRecordExists(
			array(
				'table' => 'sophie_treatment_stepgroup',
				'field' => 'label',
				'exclude' => $excludeStatement
				)
		);

		if ($dbNoRecordExistsValidator->isValid($value) === true)
		{
			$this->_error(self::MSG_NOT_EXISTS);
			return false;
		}

		return true;
	}
}