<?php
/**
 * SoPHIE Participant API Class
 *
 * The Participant API provides participant related functionality within
 * a given execution context.
 */
class Sophie_Api_Process_1_0_0_Api extends Sophie_Api_Abstract
{
	/**
	 * @var null
	 */
	protected $processService = null;
	/**
	 * @var bool
	 */
	protected $logging = true;

	/**
	 * @param $processService
	 */
	protected function setProcessService($processService)
	{
		$this->processService = $processService;
	}

	/**
	 * @return null|Sophie_Service_Session_Process
	 */
	protected function getProcessService()
	{
		if (is_null($this->processService)) {
			$this->processService = Sophie_Service_Session_Process :: getInstance();
		}
		return $this->processService;
	}

	/**
	 * Translate a special stepgroup label returning the actual label
	 *
	 * @param String $stepgroupLabel
	 * @return String|null
	 */
	public function translateStepgroupLabel($stepgroupLabel = '%current%')
	{
		return $this->getContext()->getApi('stepgroup')->translateLabel($stepgroupLabel);
	}

	 /**
	 * Translate a special step label returning the actual label
	 *
	 * @param String $stepLabel
	 * @return String|null
	 */
	public function translateStepLabel($stepLabel = '%current%')
	{
		return $this->getContext()->getApi('step')->translateLabel($stepLabel);
	}
	
	/**
	 * Translate a special stepgroup loop returning the actual loop
	 *
	 * @param String|Integer $stepgroupLoop
	 * @return Integer|null
	 */
	public function translateStepgroupLoop($stepgroupLoop = '%current%')
	{
		return $this->getContext()->getApi('stepgroup')->translateLoop($stepgroupLoop);
	}

	/**
	 * Transfer a participant to the step that is next in relation to the current context. Participant defaults to the current participant within the context.
	 * 
	 * @param String $participantLabel
	 * @return Boolean True if the transfer succeeds, false otherwise.
	 */
	public function transferParticipantToNextStep($participantLabel = '%current%')
	{
		$context = $this->getContext();
		$sessionId = $context->getSessionId();
		$stepgroupLabel = $context->getStepgroupLabel();
		$stepgroupLoop = $context->getStepgroupLoop();
		$stepId = $this->getContext()->getStepId();

		$participantLabel = $context->getApi('participant')->translateLabel($participantLabel);

		if (is_null($sessionId))
		{
			// there is no session i.e. this is a preview api call
			return false;
		}
		
		$processService = $this->getProcessService();
		return $processService->transferParticipantToNextStep($sessionId, $participantLabel, $stepgroupLabel, $stepgroupLoop, $stepId);
	}

	/**
	 * Transfer a participant to the first step in the stepgroup that is next in relation to the current context. Participant defaults to the current participant within the context.
	 * 
	 * @param String $participantLabel
	 * @return Boolean True if the transfer succeeds, false otherwise.
	 */
	public function transferParticipantToNextStepgroup($participantLabel = '%current%')
	{
		$context = $this->getContext();
		$sessionId = $context->getSessionId();
		$stepgroupLabel = $context->getStepgroupLabel();
		$stepgroupLoop = $context->getStepgroupLoop();
		$stepId = $this->getContext()->getStepId();

		$participantLabel = $context->getApi('participant')->translateLabel($participantLabel);

		if (is_null($sessionId))
		{
			// there is no session i.e. this is a preview api call
			return false;
		}
		
		$processService = $this->getProcessService();
		return $processService->transferParticipantToNextStepgroup($sessionId, $participantLabel, $stepgroupLabel, $stepgroupLoop, $stepId);
	}

	/**
	 * Transfer a participant to the first step in the current stepgroup that is next in relation to the current context and increases stepgroup loop counter by one. Participant defaults to the current participant within the context.
	 * 
	 * @param String $participantLabel
	 * @return Boolean True if the transfer succeeds, false otherwise.
	 */
	public function transferParticipantToNextStepgroupLoop($participantLabel = '%current%')
	{
		$context = $this->getContext();
		$sessionId = $context->getSessionId();
		$stepgroupLabel = $context->getStepgroupLabel();
		$stepgroupLoop = $context->getStepgroupLoop();
		$stepId = $this->getContext()->getStepId();

		$participantLabel = $context->getApi('participant')->translateLabel($participantLabel);

		if (is_null($sessionId))
		{
			// there is no session i.e. this is a preview api call
			return false;
		}
		
		$processService = $this->getProcessService();
		return $processService->transferParticipantToNextStepgroupLoop($sessionId, $participantLabel, $stepgroupLabel, $stepgroupLoop, $stepId);
	}

	/**
	 * Transfer a participant to the specified step. StepgroupLoop defaults to 1. Participant defaults to the current participant within the context.
	 * 
	 * @param String $stepLabel
	 * @param Integer $stepgroupLoop
	 * @param String $participantLabel
	 * @return Boolean True if setting the step succeeds, false otherwise.
	 */
	public function transferParticipantToStep($stepLabel, $stepgroupLoop = 1, $participantLabel = '%current%')
	{
		$context = $this->getContext();
		$participantApi = $context->getApi('participant');
		return $participantApi->setStep($stepLabel, $stepgroupLoop, $participantLabel);
	}

	/**
	 * Transfer all participant from a group to the step that is next in relation to the current context. Group defaults to the current group within the context.
	 * 
	 * @param String $groupLabel
	 * @return Boolean True if the transfer succeeds for all participants, false otherwise.
	 */
	public function transferGroupToNextStep($groupLabel = '%current%')
	{
		$context = $this->getContext();
		$groupApi = $context->getApi('group');
		$participantLabels = $groupApi->getGroupMemberLabels($groupLabel);

		$success = true;
		foreach ($participantLabels as $participantLabel) {
			$participantSuccess = $this->transferParticipantToNextStep($participantLabel);
			$success = $participantSuccess && $success;
		}
		return $success;
	}

	/**
	 * Transfer all participant from a group to the specified step. StepgroupLoop defaults to 1. Group defaults to the current group within the context.
	 * 
	 * @param String $stepLabel
	 * @param Integer $stepgroupLoop
	 * @param String $groupLabel
	 * @return Boolean True if setting the step succeeds for all participants, false otherwise.
	 */
	public function transferGroupToStep($stepLabel, $stepgroupLoop = 1, $groupLabel = '%current%')
	{
		$context = $this->getContext();
		$groupApi = $context->getApi('group');
		$participantLabels = $groupApi->getGroupMemberLabels($groupLabel);

		$success = true;
		foreach ($participantLabels as $participantLabel) {
			$participantSuccess = $this->transferParticipantToStep($stepLabel, $stepgroupLoop, $participantLabel);
			$success = $participantSuccess && $success;
		}
		return $success;
	}
	
	/**
	 * Transfer all participant to the step that is next in relation to the current context.
	 * 
	 * @return Boolean True if the transfer succeeds for all participants, false otherwise.
	 */
	public function transferEveryoneToNextStep()
	{
		$context = $this->getContext();
		$participantApi = $context->getApi('participant');
		$participantLabels = $participantApi->getParticipantLabels();

		$success = true;
		foreach ($participantLabels as $participantLabel) {
			$participantSuccess = $this->transferParticipantToNextStep($participantLabel);
			$success = $participantSuccess && $success;
		}
		return $success;
	}

	/**
	 * Transfer all participant to the specified step. StepgroupLoop defaults to 1.
	 * 
	 * @param String $stepLabel
	 * @param Integer $stepgroupLoop
	 * @return Boolean True if setting the step succeeds for all participants, false otherwise.
	 */
	public function transferEveryoneToStep($stepLabel, $stepgroupLoop = 1)
	{
		$context = $this->getContext();
		$participantApi = $context->getApi('participant');
		$participantLabels = $participantApi->getParticipantLabels();

		$success = true;
		foreach ($participantLabels as $participantLabel) {
			$participantSuccess = $this->transferParticipantToStep($stepLabel, $stepgroupLoop, $participantLabel);
			$success = $participantSuccess && $success;
		}
		return $success;
	}
}