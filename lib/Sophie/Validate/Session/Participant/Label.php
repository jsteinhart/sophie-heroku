<?php
class Sophie_Validate_Session_Participant_Label extends Sophie_Validate_AbstractLabel
{
	protected $_pattern = '/[^a-z0-9_\.]/i';
	
	public $sessionId = null;

	protected function init()
	{
		$this->_messageTemplates[self::MSG_INVALID_CHAR] = "'%value%' is not a valid participant label.";
	}
}