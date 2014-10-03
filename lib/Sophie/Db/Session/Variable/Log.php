<?php
class Sophie_Db_Session_Variable_Log extends Sophie_Db_Session_Variable
{
	// CONFIG
	public $_name = 'sophie_session_variable_log';
	public $_primary = 'id';

	private $stepId = null;
	
	public $_referenceMap = array (
		'Step' => array (
			'columns' => array (
				'stepId'
			),
			'refTableClass' => 'Sophie_Db_Treatment_Step',
			'refColumns' => array (
				'id'
			)
		),
		'Session' => array (
			'columns' => array (
				'sessionId'
			),
			'refTableClass' => 'Sophie_Db_Session',
			'refColumns' => array (
				'id'
			)
		),
	);

	protected $deleteOnNullValue = false;

	protected function createData($name, $value, $sessionId, $groupLabel = null, $participantLabel = null, $stepgroupLabel = null, $stepgroupLoop = null)
	{
		$data = parent :: createData($name, $value, $sessionId, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop);
		
		if (!is_array($data))
		{
			return;
		}

		$data['stepId'] = (is_null($this->stepId)) ? new Zend_Db_Expr('NULL') : $this->stepId;
		$data['logTime'] = new Zend_Db_Expr('NOW()');

		return $data;
	}

	/*
	 * Note:
	 * This method "logValueByNameAndContext" differs from the parent's method
	 * "setValueByNameAndContext" by the third and later arguments: Here $stepId
	 * was inserted as third argument, the parent's method's third and later
	 * arguments are shifted to the right.
	 */
	public function logValueByNameAndContext($name, $value, $stepId, $sessionId, $groupLabel = null, $participantLabel = null, $stepgroupLabel = null, $stepgroupLoop = null)
	{
		$this->stepId = $stepId;
		$data = $this->createData($name, $value, $sessionId, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop);
		if (is_array($data))
		{
			$this->insert($data);
		}
	}

}