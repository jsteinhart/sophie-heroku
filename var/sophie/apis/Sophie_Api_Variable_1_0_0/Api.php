<?php
/**
 * SoPHIE Variable API Class
 * The Variable API provides methods to interact with SoPHIE's variable system
 *
 * Getter and Setter Functions follow the form:
 * get/set + person context + procedural context
 */
class Sophie_Api_Variable_1_0_0_Api extends Sophie_Api_Abstract
{
	/**
	 * @var null
	 */
	private $variableTable = null;
	/**
	 * @var null
	 */
	private $variableLogTable = null;

	// variableTable
	/**
	 * @return null|Sophie_Db_Session_Variable
	 */
	protected function getVariableTable()
	{
		if (is_null($this->variableTable))
		{
			$this->variableTable = Sophie_Db_Session_Variable::getInstance();
		}
		return $this->variableTable;
	}

	// variableLogTable
	/**
	 * @return null|Sophie_Db_Session_Variable_Log
	 */
	protected function getVariableLogTable()
	{
		if (is_null($this->variableLogTable))
		{
			$this->variableLogTable = Sophie_Db_Session_Variable_Log::getInstance();
		}
		return $this->variableLogTable;
	}

	// Implement setter and getter functions

	// FUNCTIONS
	/**
	 * Set a variable value
	 *
	 * @param $name
	 * @param $value
	 * @param null $groupLabel
	 * @param null $participantLabel
	 * @param null $stepgroupLabel
	 * @param null $stepgroupLoop
	 * @param null $cast
	 * @return mixed
	 */
	protected function __setVariable($name, $value, $groupLabel = null, $participantLabel = null, $stepgroupLabel = null, $stepgroupLoop = null, $cast = null)
	{
		if (!is_string($name))
		{
			throw new Exception('Invalid name parameter passed to setVariable function');
			return;
		}

		if (!is_null($groupLabel) && !is_string($groupLabel))
		{
			throw new Exception('Invalid groupLabel parameter passed to setVariable function');
			return;
		}

		if (!is_null($participantLabel) && !is_string($participantLabel))
		{
			throw new Exception('Invalid participantLabel parameter passed to setVariable function');
			return;
		}

		if (!is_null($stepgroupLabel) && !is_string($stepgroupLabel))
		{
			throw new Exception('Invalid stepgroupLabel parameter passed to setVariable function');
			return;
		}

		if (!is_null($stepgroupLoop) && !is_numeric($stepgroupLoop))
		{
			throw new Exception('Invalid stepgroupLoop parameter passed to setVariable function');
			return;
		}

		$context = $this->getContext();
		$sessionId = $context->getSessionId();
		if (is_null($sessionId))
		{
			// there is no session i.e. this is a preview api call
			return;
		}

		try {
			$stepId = $context->getStepId();
		} catch (Exception $e) {
			// an exception (could be thrown by getStepId b/c wrong process context
			// level) does not matter
			$stepId = null;
		}


		if (!is_null($cast)) {
			$value = $this->getVariableTable()->castValue($value, $cast);
		}
		// insert/update value to value table:
		$result = $this->getVariableTable()->setValueByNameAndContext($name, $value, $sessionId, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop);
		// insert value to log table:
		$this->getVariableLogTable()->logValueByNameAndContext($name, $value, $stepId, $sessionId, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop);
		return $result;
	}

	/**
	 * Get a variable value
	 *
	 * @param $name
	 * @param null $groupLabel
	 * @param null $participantLabel
	 * @param null $stepgroupLabel
	 * @param null $stepgroupLoop
	 * @param null $cast
	 * @return mixed
	 */
	protected function __getVariable($name, $groupLabel = null, $participantLabel = null, $stepgroupLabel = null, $stepgroupLoop = null, $cast = null)
	{
		if (!is_string($name))
		{
			throw new Exception('Invalid name parameter passed to setVariable function');
			return;
		}

		if (!is_null($groupLabel) && !is_string($groupLabel))
		{
			throw new Exception('Invalid groupLabel parameter passed to setVariable function');
			return;
		}

		if (!is_null($participantLabel) && !is_string($participantLabel))
		{
			throw new Exception('Invalid participantLabel parameter passed to setVariable function');
			return;
		}

		if (!is_null($stepgroupLabel) && !is_string($stepgroupLabel))
		{
			throw new Exception('Invalid stepgroupLabel parameter passed to setVariable function');
			return;
		}

		if (!is_null($stepgroupLoop) && !is_numeric($stepgroupLoop))
		{
			throw new Exception('Invalid stepgroupLoop parameter passed to setVariable function');
			return;
		}

		$sessionId = $this->getContext()->getSessionId();
		if (is_null($sessionId))
		{
			// there is no session i.e. this is a preview api call
			return '{' . $name . '}';
		}

		$value = $this->getVariableTable()->fetchValueByNameAndContext($name, $sessionId, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop);
		if (!is_null($cast)) {
			$this->getVariableTable()->castValue($value, $cast);
		}
		return $value;
	}

	/**
	 * Delete a variable value
	 *
	 * @param $name
	 * @param null $groupLabel
	 * @param null $participantLabel
	 * @param null $stepgroupLabel
	 * @param null $stepgroupLoop
	 * @return mixed
	 */
	protected function __deleteVariable($name, $groupLabel = null, $participantLabel = null, $stepgroupLabel = null, $stepgroupLoop = null)
	{
		$sessionId = $this->getContext()->getSessionId();
		return $this->getVariableTable()->unsetValueByNameAndContext($name, $sessionId, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop);
	}

	// Everyone, Everywhere
	/**
	 * Get the value of a everyone and everywhere context variable
	 *
	 * @param $name
	 * @param null $cast
	 * @return mixed
	 */
	public function getEE($name, $cast = null)
	{
		$stepgroupLabel = null;
		$stepgroupLoop = null;
		$groupLabel = null;
		$participantLabel = null;

		return $this->__getVariable($name, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop, $cast);
	}

	/**
	 * Set the value of a everyone and everywhere context variable
	 *
	 * @param $name
	 * @param null $value
	 * @param null $cast
	 * @return mixed
	 */
	public function setEE($name, $value = null, $cast = null)
	{
		$stepgroupLabel = null;
		$stepgroupLoop = null;
		$groupLabel = null;
		$participantLabel = null;

		return $this->__setVariable($name, $value, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop, $cast);
	}

	// Everyone, Stepgroup
	/**
	 * Set the value of a everyone and stepgroup context variable
	 *
	 * @param $name
	 * @param string $stepgroupLabel
	 * @param null $cast
	 * @return mixed
	 */
	public function getES($name, $stepgroupLabel = '%current%', $cast = null)
	{
		$stepgroupLabel = $this->getContext()->getApi('process')->translateStepgroupLabel($stepgroupLabel);
		$stepgroupLoop = null;
		$groupLabel = null;
		$participantLabel = null;

		return $this->__getVariable($name, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop, $cast);
	}

	/**
	 * Set the value of a everyone and stepgroup context variable
	 *
	 * @param $name
	 * @param null $value
	 * @param string $stepgroupLabel
	 * @param null $cast
	 * @return mixed
	 */
	public function setES($name, $value = null, $stepgroupLabel = '%current%', $cast = null)
	{
		$stepgroupLabel = $this->getContext()->getApi('process')->translateStepgroupLabel($stepgroupLabel);
		$stepgroupLoop = null;
		$groupLabel = null;
		$participantLabel = null;

		return $this->__setVariable($name, $value, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop, $cast);
	}

	// Everyone, StepgroupLoop
	/**
	 * Get the value of a everyone and stepgroup loop context variable
	 *
	 * @param $name
	 * @param string $stepgroupLabel
	 * @param string $stepgroupLoop
	 * @param null $cast
	 * @return mixed
	 */
	public function getESL($name, $stepgroupLabel = '%current%', $stepgroupLoop = '%current%', $cast = null)
	{
		$stepgroupLabel = $this->getContext()->getApi('process')->translateStepgroupLabel($stepgroupLabel);
		$stepgroupLoop = $this->getContext()->getApi('process')->translateStepgroupLoop($stepgroupLoop, array('stepgroupLabel' => $stepgroupLabel));
		$groupLabel = null;
		$participantLabel = null;

		return $this->__getVariable($name, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop, $cast);
	}

	/**
	 * Set the value of a everyone and stepgroup loop context variable
	 *
	 * @param $name
	 * @param null $value
	 * @param string $stepgroupLabel
	 * @param string $stepgroupLoop
	 * @param null $cast
	 * @return mixed
	 */
	public function setESL($name, $value = null, $stepgroupLabel = '%current%', $stepgroupLoop = '%current%', $cast = null)
	{
		$stepgroupLabel = $this->getContext()->getApi('process')->translateStepgroupLabel($stepgroupLabel);
		$stepgroupLoop = $this->getContext()->getApi('process')->translateStepgroupLoop($stepgroupLoop, array('stepgroupLabel' => $stepgroupLabel));
		$groupLabel = null;
		$participantLabel = null;

		return $this->__setVariable($name, $value, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop, $cast);
	}

	// Group, Everywhere
	/**
	 * Get the value of a group and everywhere context variable
	 *
	 * @param $name
	 * @param string $groupLabel
	 * @param null $cast
	 * @return mixed
	 */
	public function getGE($name, $groupLabel = '%current%', $cast = null)
	{
		$stepgroup = $this->getContext()->getStepgroup();

		$stepgroupLabel = null;
		$stepgroupLoop = null;
		$groupLabel = $this->getContext()->getApi('group')->translateLabel($groupLabel);
		$participantLabel = null;

		return $this->__getVariable($name, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop, $cast);
	}

	/**
	 * Set the value of a group and everywhere context variable
	 *
	 * @param $name
	 * @param null $value
	 * @param null $groupLabel
	 * @param null $cast
	 * @return mixed
	 */
	public function setGE($name, $value = null, $groupLabel = null, $cast = null)
	{
		$stepgroup = $this->getContext()->getStepgroup();

		$stepgroupLabel = null;
		$stepgroupLoop = null;
		$groupLabel = $this->getContext()->getApi('group')->translateLabel($groupLabel);
		$participantLabel = null;

		return $this->__setVariable($name, $value, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop, $cast);
	}

	// Group, Stepgroup
	/**
	 * Get the value of a group and stepgroup context variable
	 *
	 * @param $name
	 * @param null $groupLabel
	 * @param null $stepgroupLabel
	 * @param null $cast
	 * @return mixed
	 */
	public function getGS($name, $groupLabel = null, $stepgroupLabel = null, $cast = null)
	{
		$stepgroup = $this->getContext()->getStepgroup();

		$stepgroupLabel = $this->getContext()->getApi('process')->translateStepgroupLabel($stepgroupLabel);
		$stepgroupLoop = null;
		$groupLabel = $this->getContext()->getApi('group')->translateLabel($groupLabel, array('stepgroupLabel' => $stepgroupLabel));
		$participantLabel = null;

		return $this->__getVariable($name, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop, $cast);
	}

	/**
	 * Set the value of a group and stepgroup context variable
	 *
	 * @param $name
	 * @param null $value
	 * @param null $groupLabel
	 * @param null $stepgroupLabel
	 * @param null $cast
	 * @return mixed
	 */
	public function setGS($name, $value = null, $groupLabel = null, $stepgroupLabel = null, $cast = null)
	{
		$stepgroup = $this->getContext()->getStepgroup();

		$stepgroupLabel = $this->getContext()->getApi('process')->translateStepgroupLabel($stepgroupLabel);
		$stepgroupLoop = null;
		$groupLabel = $this->getContext()->getApi('group')->translateLabel($groupLabel, array('stepgroupLabel' => $stepgroupLabel));
		$participantLabel = null;

		return $this->__setVariable($name, $value, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop, $cast);
	}

	// Group, StepgroupLoop
	/**
	 * Get the value of a group and stepgroup loop context variable
	 *
	 * @param $name
	 * @param string $groupLabel
	 * @param string $stepgroupLabel
	 * @param string $stepgroupLoop
	 * @param null $cast
	 * @return mixed
	 */
	public function getGSL($name, $groupLabel = '%current%', $stepgroupLabel = '%current%', $stepgroupLoop = '%current%', $cast = null)
	{
		$stepgroup = $this->getContext()->getStepgroup();

		$stepgroupLabel = $this->getContext()->getApi('process')->translateStepgroupLabel($stepgroupLabel);
		$stepgroupLoop = $this->getContext()->getApi('process')->translateStepgroupLoop($stepgroupLoop, array('stepgroupLabel' => $stepgroupLabel));
		$groupLabel = $this->getContext()->getApi('group')->translateLabel($groupLabel, array('stepgroupLabel' => $stepgroupLabel, 'stepgroupLoop' => $stepgroupLoop));
		$participantLabel = null;

		return $this->__getVariable($name, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop, $cast);
	}

	/**
	 * Set the value of a group and stepgroup loop context variable
	 *
	 * @param $name
	 * @param null $value
	 * @param string $groupLabel
	 * @param string $stepgroupLabel
	 * @param string $stepgroupLoop
	 * @param null $cast
	 * @return mixed
	 */
	public function setGSL($name, $value = null, $groupLabel = '%current%', $stepgroupLabel = '%current%', $stepgroupLoop = '%current%', $cast = null)
	{
		$stepgroup = $this->getContext()->getStepgroup();

		$stepgroupLabel = $this->getContext()->getApi('process')->translateStepgroupLabel($stepgroupLabel);
		$stepgroupLoop = $this->getContext()->getApi('process')->translateStepgroupLoop($stepgroupLoop, array('stepgroupLabel' => $stepgroupLabel));
		$groupLabel = $this->getContext()->getApi('group')->translateLabel($groupLabel, array('stepgroupLabel' => $stepgroupLabel, 'stepgroupLoop' => $stepgroupLoop));
		$participantLabel = null;

		return $this->__setVariable($name, $value, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop, $cast);
	}

	// Participant, Everywhere
	/**
	 * Get the value of a participant and everywhere context variable
	 *
	 * @param $name
	 * @param string $participantLabel
	 * @param null $cast
	 * @return mixed
	 */
	public function getPE($name, $participantLabel = '%current%', $cast = null)
	{
		$stepgroupLabel = null;
		$stepgroupLoop = null;
		$groupLabel = null;
		$participantLabel = $this->getContext()->getApi('participant')->translateLabel($participantLabel);

		return $this->__getVariable($name, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop, $cast);
	}

	/**
	 * Set the value of a participant and everywhere context variable
	 *
	 * @param $name
	 * @param null $value
	 * @param string $participantLabel
	 * @param null $cast
	 * @return mixed
	 */
	public function setPE($name, $value = null, $participantLabel = '%current%', $cast = null)
	{
		$stepgroupLabel = null;
		$stepgroupLoop = null;
		$groupLabel = null;
		$participantLabel = $this->getContext()->getApi('participant')->translateLabel($participantLabel);

		return $this->__setVariable($name, $value, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop, $cast);
	}

	// Participant, Stepgroup
	/**
	 * Get the value of a participant and stepgroup context variable
	 *
	 * @param $name
	 * @param string $participantLabel
	 * @param string $stepgroupLabel
	 * @param null $cast
	 * @return mixed
	 */
	public function getPS($name, $participantLabel = '%current%', $stepgroupLabel = '%current%', $cast = null)
	{
		$stepgroupLabel = $this->getContext()->getApi('process')->translateStepgroupLabel($stepgroupLabel);
		$stepgroupLoop = null;
		$groupLabel = null;
		$participantLabel = $this->getContext()->getApi('participant')->translateLabel($participantLabel, array('stepgroupLabel' => $stepgroupLabel));

		return $this->__getVariable($name, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop, $cast);
	}

	/**
	 * Set the value of a participant and stepgroup context variable
	 *
	 * @param $name
	 * @param null $value
	 * @param string $participantLabel
	 * @param string $stepgroupLabel
	 * @param null $cast
	 * @return mixed
	 */
	public function setPS($name, $value = null, $participantLabel = '%current%', $stepgroupLabel = '%current%', $cast = null)
	{
		$stepgroupLabel = $this->getContext()->getApi('process')->translateStepgroupLabel($stepgroupLabel);
		$stepgroupLoop = null;
		$groupLabel = null;
		$participantLabel = $this->getContext()->getApi('participant')->translateLabel($participantLabel, array('stepgroupLabel' => $stepgroupLabel));

		return $this->__setVariable($name, $value, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop, $cast);
	}

	// Participant, StepgroupLoop
	/**
	 * Get the value of a participant and stepgroup loop context variable
	 *
	 * @param $name
	 * @param string $participantLabel
	 * @param string $stepgroupLabel
	 * @param string $stepgroupLoop
	 * @param null $cast
	 * @return mixed
	 */
	public function getPSL($name, $participantLabel = '%current%', $stepgroupLabel = '%current%', $stepgroupLoop = '%current%', $cast = null)
	{
		$stepgroupLabel = $this->getContext()->getApi('process')->translateStepgroupLabel($stepgroupLabel);
		$stepgroupLoop = $this->getContext()->getApi('process')->translateStepgroupLoop($stepgroupLoop, array('stepgroupLabel' => $stepgroupLabel));
		$groupLabel = null;
		$participantLabel = $this->getContext()->getApi('participant')->translateLabel($participantLabel, array('stepgroupLabel' => $stepgroupLabel, 'stepgroupLoop' => $stepgroupLoop));

		return $this->__getVariable($name, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop, $cast);
	}

	/**
	 * Set the value of a participant and stepgroup loop context variable
	 *
	 * @param $name
	 * @param null $value
	 * @param string $participantLabel
	 * @param string $stepgroupLabel
	 * @param string $stepgroupLoop
	 * @param null $cast
	 * @return mixed
	 */
	public function setPSL($name, $value = null, $participantLabel = '%current%', $stepgroupLabel = '%current%', $stepgroupLoop = '%current%', $cast = null)
	{
		$stepgroupLabel = $this->getContext()->getApi('process')->translateStepgroupLabel($stepgroupLabel);
		$stepgroupLoop = $this->getContext()->getApi('process')->translateStepgroupLoop($stepgroupLoop, array('stepgroupLabel' => $stepgroupLabel));
		$groupLabel = null;
		$participantLabel = $this->getContext()->getApi('participant')->translateLabel($participantLabel, array('stepgroupLabel' => $stepgroupLabel, 'stepgroupLoop' => $stepgroupLoop));

		return $this->__setVariable($name, $value, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop, $cast);
	}
}