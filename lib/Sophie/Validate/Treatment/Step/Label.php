<?php
class Sophie_Validate_Treatment_Step_Label extends Sophie_Validate_AbstractLabel
{
	public $treatmentId = null;
	public $stepId = null;
	
	protected function init()
	{
		$this->_messageTemplates[self::MSG_NOT_UNIQUE] = "'%value%' is not a unique step label within the treatment.";
	}

	public function uniqueCheck($value)
	{
		if (is_null($this->treatmentId))
		{
			throw new Exception('TreatmentId needs to be passed to ' . __CLASS__ . ' for Uniqueness check.');
		}

		// validate uniqueness with Zend_Validate_Db_NoRecordExists:
		$stepgroupIds = Sophie_Db_Treatment_Stepgroup::getInstance()->getIdsByTreatment($this->treatmentId);
		$excludeStatement = 'stepgroupId IN (' . implode(',', $stepgroupIds). ')';

		if (!is_null($this->stepId))
		{
			$excludeStatement .= ' AND id != ' . (int)$this->stepId;
		}

		$dbNoRecordExistsValidator = new Zend_Validate_Db_NoRecordExists(
			array(
				'table' => 'sophie_treatment_step',
				'field' => 'label',
				'exclude' => $excludeStatement
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