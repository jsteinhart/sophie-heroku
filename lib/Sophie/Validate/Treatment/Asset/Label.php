<?php
class Sophie_Validate_Treatment_Asset_Label extends Sophie_Validate_AbstractLabel
{
	protected $_patternCheck = false;

	public $treatmentId = null;
	public $assetId = null;

	protected function init()
	{
		$this->_messageTemplates[self::MSG_NOT_UNIQUE] = "'%value%' is not a unique asset label within the treatment.";
	}

	public function uniqueCheck($value)
	{
		if (is_null($this->treatmentId))
		{
			throw new Exception('TreatmentId needs to be passed to ' . __CLASS__ . ' for Uniqueness check.');
		}

		// validate uniqueness with Zend_Validate_Db_NoRecordExists:

		$excludeStatement = 'treatmentId = ' . (int)$this->treatmentId;

		if (!is_null($this->assetId))
		{
			$excludeStatement .= ' AND id != ' . (int)$this->assetId;
		}

		$dbNoRecordExistsValidator = new Zend_Validate_Db_NoRecordExists(
			array(
				'table' => 'sophie_treatment_asset',
				'field' => 'label',
				'exclude' => $excludeStatement,
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