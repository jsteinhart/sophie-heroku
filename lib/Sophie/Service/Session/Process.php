<?php
class Sophie_Service_Session_Process
{
	// SINGLETON
	static protected $_instance = null;
	static public function getInstance()
	{
		if (null === self :: $_instance)
		{
			self :: $_instance = new self;
		}
		return self :: $_instance;
	}

	// FUNCTIONS
	public function transferParticipantToNextStep($sessionId, $participantLabel, $fromStepgroupLabel = null, $fromStepgroupLoop = null, $fromStepId = null)
	{
		$participant = Sophie_Db_Session_Participant :: getInstance()->fetchRowBySessionAndLabel($sessionId, $participantLabel);

		if ((!is_null($fromStepgroupLabel) && $participant->stepgroupLabel != $fromStepgroupLabel) || (!is_null($fromStepgroupLoop) && $participant->stepgroupLoop != $fromStepgroupLoop) || (!is_null($fromStepId) && $participant->stepId != $fromStepId))
		{
			Sophie_Db_Session_Log :: log($sessionId, 'Transfer participant ' . $participant->id . ' to next step failed because participant is already in a different place', null, print_r($participant->toArray(), true));
			return;
		}

		$session = $participant->findParentRow('Sophie_Db_Session');
		$currentStepgroup = Sophie_Db_Treatment_Stepgroup :: getInstance()->fetchByLabel($session->treatmentId, $participant->stepgroupLabel);
		$currentStep = $participant->findParentRow('Sophie_Db_Treatment_Step')->toArray();


		// prepare log entry, will be set later on if there is no error
		$logEntryLeave = new Sophie_Session_Log_Entry($sessionId);
		$logEntryLeave->participantLabel = $participant->label;
		$logEntryLeave->stepgroupLabel = $currentStepgroup['label'];
		$logEntryLeave->stepgroupLoop = $participant->stepgroupLoop;
		$logEntryLeave->stepLabel = $currentStep['label'];
		$logEntryLeave->content = 'Participant ' . $participant->label . ' leaves step "' . $currentStep['label'] . '"';
		$logEntryLeave->contentId = 'Sophie_Leave_Step';
		$logEntryLeave->type = 'event';

		$newStepFinal = false;
		do
		{
			if (isset ($newStepgroup))
				$currentStepgroup = $newStepgroup;

			if (isset ($newStep))
				$currentStep = $newStep;

			// GOTO NEXT STEP IN STEPGROUP
			$newStep = Sophie_Db_Treatment_Step :: getInstance()->fetchRowByStepgroupLabelPositionAndType($session->treatmentId, $participant->stepgroupLabel, $participant->typeLabel, ($currentStep['position'] + 1));
			if (!is_null($newStep))
			{
				$newStep = $newStep->toArray();
				$participant->stepId = $newStep['id'];
				$newStepgroup = $currentStepgroup;
				$newStepFinal = true;
			}

			// NO STEP IN STEPGROUP LEFT
			else
			{
				// CHECK IF WE HAVE TO TAKE ANOTHER STEPGROUP LOOP OR IF THE STEPGROUP USES INFINITE LOOPS
				if ($participant->stepgroupLoop < $currentStepgroup['loop'] || $currentStepgroup['loop'] == -1)
				{
					$newStep = Sophie_Db_Treatment_Step :: getInstance()->fetchRowByStepgroupLabelPositionAndType($session->treatmentId, $participant->stepgroupLabel, $participant->typeLabel, 1);
					// no step in stepgroup available set to last loop and continue to new step
					if (is_null($newStep))
					{
						$newStep = $currentStep;
						$newStepgroup = $currentStepgroup;
						$participant->stepgroupLoop = $currentStepgroup->loop;
						// go through the next step loop again
						$newStepFinal = false;
					}
					else
					{
						$newStep = $newStep->toArray();
						$participant->stepId = $newStep['id'];
						$participant->stepgroupLoop = $participant->stepgroupLoop + 1;
						$newStepgroup = $currentStepgroup;
						$newStepFinal = true;
					}
				}

				// NO LOOPS LEFT, GO TO NEXT STEPGROUP
				else
				{
					// GET NEXT STEPGROUP IN TREATMENT
					do
					{

						$newStepgroup = Sophie_Db_Treatment_Stepgroup :: getInstance()->fetchRowByTreatmentPositionActiveNonEmpty($session->treatmentId, $participant->typeLabel, ($currentStepgroup['position'] + 1));
						if ($newStepgroup !== false && !is_null($newStepgroup))
						{
							$newStep = Sophie_Db_Treatment_Step :: getInstance()->fetchRowByStepgroupLabelPositionAndType($session->treatmentId, $newStepgroup['label'], $participant->typeLabel, 1);
							// no step found in treatment go to next stepgroup
							if (is_null($newStep))
							{
								$currentStepgroup['position'] += 1;
								continue;
							}

							else
							{
								$newStep = $newStep->toArray();
								$participant->stepId = $newStep['id'];
								$participant->stepgroupLoop = 1;
								$participant->stepgroupLabel = $newStepgroup['label'];
								$newStepFinal = true;
							}

						}

						// NO STEPGROUP LEFT IN TREATMENT
						else
						{
							$participant->state = 'finished';
							Sophie_Db_Session_Log :: log($sessionId, 'Participant ' . $participant->label . ' finished the last step');
							$newStepFinal = true;
						}
					}
					while ($participant->state != 'finished' && !$newStepFinal);
				}
			}
		}
		while ($participant->state != 'finished' && !$newStepFinal);

		Sophie_Db_Session_Log :: log($logEntryLeave);
		$participant->save();

		if ($participant->state != 'finished')
		{
			$this->logEnterStep($sessionId, $participant, $newStep['label']);
		}
	}

	public function transferParticipantToNextStepgroupLoop($sessionId, $participantLabel, $fromStepgroupLabel = null, $fromStepgroupLoop = null, $fromStepId = null)
	{
		$participant = Sophie_Db_Session_Participant :: getInstance()->fetchRowBySessionAndLabel($sessionId, $participantLabel);

		if ((!is_null($fromStepgroupLabel) && $participant->stepgroupLabel != $fromStepgroupLabel) || (!is_null($fromStepgroupLoop) && $participant->stepgroupLoop != $fromStepgroupLoop) || (!is_null($fromStepId) && $participant->stepId != $fromStepId))
		{
			Sophie_Db_Session_Log :: log($sessionId, 'Transfer participant ' . $participant->id . ' to next stepgroupLoop failed because participant is already in a different place', null, print_r($participant->toArray(), true));
			return;
		}

		$session = $participant->findParentRow('Sophie_Db_Session');
		$currentStepgroup = Sophie_Db_Treatment_Stepgroup :: getInstance()->fetchByLabel($session->treatmentId, $participant->stepgroupLabel);
		$currentStep = $participant->findParentRow('Sophie_Db_Treatment_Step')->toArray();

		// prepare log entry, will be set later on if there is no error
		$logEntryLeave = new Sophie_Session_Log_Entry($sessionId);
		$logEntryLeave->participantLabel = $participant->label;
		$logEntryLeave->stepgroupLabel = $currentStepgroup['label'];
		$logEntryLeave->stepgroupLoop = $participant->stepgroupLoop;
		$logEntryLeave->stepLabel = $currentStep['label'];
		$logEntryLeave->content = 'Participant ' . $participant->label . ' leaves step "' . $currentStep['label'] . '"';
		$logEntryLeave->contentId = 'Sophie_Leave_Step';
		$logEntryLeave->type = 'event';

		$newStepFinal = false;
		do
		{
			if (isset ($newStepgroup))
				$currentStepgroup = $newStepgroup;

			if (isset ($newStep))
				$currentStep = $newStep;

			// CHECK IF WE HAVE TO TAKE ANOTHER STEPGROUP LOOP OR IF THE STEPGROUP USES INFINITE LOOPS
			if ($participant->stepgroupLoop < $currentStepgroup['loop'] || $currentStepgroup['loop'] == -1)
			{
				$newStep = Sophie_Db_Treatment_Step :: getInstance()->fetchRowByStepgroupLabelPositionAndType($session->treatmentId, $participant->stepgroupLabel, $participant->typeLabel, 1);
				// no step in stepgroup available set to last loop and continue to new step
				if (is_null($newStep))
				{
					$newStep = $currentStep;
					$newStepgroup = $currentStepgroup;
					$participant->stepgroupLoop = $currentStepgroup->loop;
					// go through the next step loop again
					$newStepFinal = false;
				}
				else
				{
					$newStep = $newStep->toArray();
					$participant->stepId = $newStep['id'];
					$participant->stepgroupLoop = $participant->stepgroupLoop + 1;
					$newStepgroup = $currentStepgroup;
					$newStepFinal = true;
				}
			}

			// NO LOOPS LEFT, GO TO NEXT STEPGROUP
			else
			{
				// GET NEXT STEPGROUP IN TREATMENT
				do
				{

					$newStepgroup = Sophie_Db_Treatment_Stepgroup :: getInstance()->fetchRowByTreatmentPositionActiveNonEmpty($session->treatmentId, $participant->typeLabel, ($currentStepgroup['position'] + 1));
					if ($newStepgroup !== false && !is_null($newStepgroup))
					{
						$newStep = Sophie_Db_Treatment_Step :: getInstance()->fetchRowByStepgroupLabelPositionAndType($session->treatmentId, $newStepgroup['label'], $participant->typeLabel, 1);
						// no step found in treatment go to next stepgroup
						if (is_null($newStep))
						{
							$currentStepgroup['position'] += 1;
							continue;
						}

						else
						{
							$newStep = $newStep->toArray();
							$participant->stepId = $newStep['id'];
							$participant->stepgroupLoop = 1;
							$participant->stepgroupLabel = $newStepgroup['label'];
							$newStepFinal = true;
						}

					}

					// NO STEPGROUP LEFT IN TREATMENT
					else
					{
						$participant->state = 'finished';
						Sophie_Db_Session_Log :: log($sessionId, 'Participant ' . $participant->label . ' finished the last step');
						$newStepFinal = true;
					}
				}
				while($participant->state != 'finished' && !$newStepFinal);
			}
		}
		while ($participant->state != 'finished' && !$newStepFinal);

		Sophie_Db_Session_Log :: log($logEntryLeave);
		$participant->save();

		if ($participant->state != 'finished')
		{
			$this->logEnterStep($sessionId, $participant, $newStep['label']);
		}
	}

	public function transferParticipantToNextStepgroup($sessionId, $participantLabel, $fromStepgroupLabel = null, $fromStepgroupLoop = null, $fromStepId = null)
	{
		$participant = Sophie_Db_Session_Participant :: getInstance()->fetchRowBySessionAndLabel($sessionId, $participantLabel);

		if ((!is_null($fromStepgroupLabel) && $participant->stepgroupLabel != $fromStepgroupLabel) || (!is_null($fromStepgroupLoop) && $participant->stepgroupLoop != $fromStepgroupLoop) || (!is_null($fromStepId) && $participant->stepId != $fromStepId))
		{
			Sophie_Db_Session_Log :: log($sessionId, 'Transfer participant ' . $participant->id . ' to next step failed because participant is already in a different place', null, print_r($participant->toArray(), true));
			return;
		}

		$session = $participant->findParentRow('Sophie_Db_Session');
		$currentStepgroup = Sophie_Db_Treatment_Stepgroup :: getInstance()->fetchByLabel($session->treatmentId, $participant->stepgroupLabel);
		$currentStep = $participant->findParentRow('Sophie_Db_Treatment_Step')->toArray();


		// prepare log entry, will be set later on if there is no error
		$logEntryLeave = new Sophie_Session_Log_Entry($sessionId);
		$logEntryLeave->participantLabel = $participant->label;
		$logEntryLeave->stepgroupLabel = $currentStepgroup['label'];
		$logEntryLeave->stepgroupLoop = $participant->stepgroupLoop;
		$logEntryLeave->stepLabel = $currentStep['label'];
		$logEntryLeave->content = 'Participant ' . $participant->label . ' leaves step "' . $currentStep['label'] . '"';
		$logEntryLeave->contentId = 'Sophie_Leave_Step';
		$logEntryLeave->type = 'event';

		$newStepFinal = false;

		// GET NEXT STEPGROUP IN TREATMENT
		do
		{
			$newStepgroup = Sophie_Db_Treatment_Stepgroup :: getInstance()->fetchRowByTreatmentPositionActiveNonEmpty($session->treatmentId, $participant->typeLabel, ($currentStepgroup['position'] + 1));
			if ($newStepgroup !== false && !is_null($newStepgroup))
			{
				$newStep = Sophie_Db_Treatment_Step :: getInstance()->fetchRowByStepgroupLabelPositionAndType($session->treatmentId, $newStepgroup['label'], $participant->typeLabel, 1);
				// no step found in treatment go to next stepgroup
				if (is_null($newStep))
				{
					$currentStepgroup['position'] += 1;
					continue;
				}

				else
				{
					$newStep = $newStep->toArray();
					$participant->stepId = $newStep['id'];
					$participant->stepgroupLoop = 1;
					$participant->stepgroupLabel = $newStepgroup['label'];
					$newStepFinal = true;
				}

			}

			// NO STEPGROUP LEFT IN TREATMENT
			else
			{
				$participant->state = 'finished';
				Sophie_Db_Session_Log :: log($sessionId, 'Participant ' . $participant->label . ' finished the last step');
				$newStepFinal = true;
			}
		}
		while ($participant->state != 'finished' && !$newStepFinal);

		Sophie_Db_Session_Log :: log($logEntryLeave);

		$participant->save();

		if ($participant->state != 'finished')
		{
			$this->logEnterStep($sessionId, $participant, $newStep['label']);
		}
	}

	public function initializeParticipant($sessionId, $participantLabel)
	{
		$participant = Sophie_Db_Session_Participant :: getInstance()->fetchRowBySessionAndLabel($sessionId, $participantLabel);
		if (is_null($participant))
		{
			throw new Exception('Unknown participant');
		}
		$session = $participant->findParentRow('Sophie_Db_Session');

		if ($participant->state != 'new')
		{
			$error = 'Participant ' . $participantLabel . ' is already initialized.';
			$logEntry = new Sophie_Session_Log_Entry($sessionId);
			$logEntry->participantLabel = $participant->label;
			$logEntry->type = 'error';
			$logEntry->content = $error;
			$logEntry->contentId = 'Sophie_Init_Participant_Error_Already';
			Sophie_Db_Session_Log :: log($logEntry);
			throw new Exception($error);
		}
		
		$stepgroup = Sophie_Db_Treatment_Stepgroup :: getInstance()->fetchRowByTreatmentPositionActiveNonEmpty($session->treatmentId, $participant->typeLabel);
		if ($stepgroup === false)
		{
			$error = 'Finding first step for participant ' . $participant->label . ' failed: First stepgroup does not exists.';
			$logEntry = new Sophie_Session_Log_Entry($sessionId);
			$logEntry->participantLabel = $participant->label;
			$logEntry->type = 'error';
			$logEntry->content = $error;
			$logEntry->contentId = 'Sophie_Init_Participant_Error_Failed_Stepgroup';
			Sophie_Db_Session_Log :: log($logEntry);
			throw new Exception($error);
		}

		$participant->stepgroupLabel = $stepgroup['label'];
		$participant->stepgroupLoop = 1;

		$step = Sophie_Db_Treatment_Step :: getInstance()->fetchRowByStepgroupLabelPositionAndType($session->treatmentId, $participant->stepgroupLabel, $participant->typeLabel);

		if ($step === false)
		{
			$error = 'Finding first step for participant ' . $participant->label . ' failed: First step does not exist.';
			$logEntry = new Sophie_Session_Log_Entry($sessionId);
			$logEntry->participantLabel = $participant->label;
			$logEntry->type = 'error';
			$logEntry->content = $error;
			$logEntry->contentId = 'Sophie_Init_Participant_Error_Failed_Step';
			Sophie_Db_Session_Log :: log($logEntry);
			throw new Exception($error);
		}

		$participant->stepId = $step['id'];
		$participant->state = 'started';
		$participant->lastContact = microtime(true);

		$participant->save();
		$this->logEnterStep($sessionId, $participant, $step['label']);
		return $participant->toArray();
	}

	private function logEnterStep($sessionId, $participant, $stepLabel)
	{
		$logEntry = new Sophie_Session_Log_Entry($sessionId);
		$logEntry->participantLabel = $participant->label;
		$logEntry->stepgroupLabel = $participant->stepgroupLabel;
		$logEntry->stepgroupLoop = $participant->stepgroupLoop;
		$logEntry->stepLabel = $stepLabel;
		$logEntry->content = 'Participant ' . $participant->label . ' enters step "' . $stepLabel . '"';
		$logEntry->contentId = 'Sophie_Enter_Step';
		$logEntry->type = 'event';
		Sophie_Db_Session_Log :: log($logEntry);
	}
}