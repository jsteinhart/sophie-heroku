<?php
class Sophie_Validate_Treatment_Report_Name extends Sophie_Validate_AbstractLabel
{
	protected $_patternCheck = false;

	public $treatmentId = null;
	public $reportId = null;

	protected function init()
	{
		$this->_messageTemplates[self::MSG_NOT_UNIQUE] = "'%value%' is not a unique report name within the treatment.";
	}

	public function uniqueCheck($value)
	{
		if (is_null($this->treatmentId))
		{
			throw new Exception('TreatmentId needs to be passed to ' . __CLASS__ . ' for Uniqueness check.');
		}

		// validate uniqueness with Zend_Validate_Db_NoRecordExists:

		$excludeStatement = 'treatmentId = ' . (int)$this->treatmentId;

		if (!is_null($this->reportId))
		{
			$excludeStatement .= ' AND id != ' . (int)$this->reportId;
		}

		$dbNoRecordExistsValidator = new Zend_Validate_Db_NoRecordExists(
			array(
				'table' => 'sophie_treatment_report',
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