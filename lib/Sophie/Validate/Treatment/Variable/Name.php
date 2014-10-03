<?php
class Sophie_Validate_Treatment_Variable_Name extends Sophie_Validate_AbstractLabel
{
	public $treatmentId = null;
	public $variableId = null;

	public $participantLabel = null;
	public $participantGroup = null;

	public $stepgroupLabel = null;
	public $stepgroupLoop = null;
	
	protected function init()
	{
		$this->_messageTemplates[self::MSG_INVALID_CHAR] = "'%value%' is not a valid variable name. Use Alphabet, Numbers and _ only.";
	}
}