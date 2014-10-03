<?php
/**
 * SoPHIE Participant API Class
 *
 * The Participant API provides participant related functionality within
 * the execution context.
 */
class Sophie_Api_Participant_1_0_0_Api extends Sophie_Api_Abstract
{
	/**
	 * @var Sophie_Db_Session_Participant
	 */
	protected $participantTable = null;

	/**
	 * Set the internal participant database table object.
	 *
	 * @param Sophie_Db_Session_Participant $participantTable
	 */
	protected function setParticipantTable(Sophie_Db_Session_Participant $participantTable)
	{
		$this->participantTable = $participantTable;
	}

	/**
	 * Get the internal participant database table object.
	 *
	 * @return Sophie_Db_Session_Participant
	 */
	protected function getParticipantTable()
	{
		if (is_null($this->participantTable)) {
			$this->participantTable = Sophie_Db_Session_Participant::getInstance();
		}
		return $this->participantTable;
	}

	/**
	 * Translates a participant special label considering the context into a label of a specific participant.
	 *
	 * The method supports the special labels %current%, %partner% and %localPartner%.
	 * %current% translates to the label  of the participant which is currently active within the procedural context.
	 * %partner% translates to the label of a second participant within the current group besides the curretly active participant within the current procedural context.
	 * %localPartner% translates to the label of a second participant within the group of a specified procedural context besides the curretly active participant. The procedural context is defined by passing stepgroupLabel and stepgroupLoop as options in an array as a second parameter to the method.
	 *
	 * @param String $label Participant special label to be translated. Defaults to %current%.
	 * @param Array $options Array of options stepgroupLabel and stepgroupLoop for %localPartner%
	 * @return String|null Participant Label
	 */
	public function translateLabel($label, $options = array())
	{
		if (is_null($label) || $label === '%current%') {
			return $this->getContext()->getParticipantLabel();
		}

		$sessionId = $this->getContext()->getSessionId();
		if (is_null($sessionId))
		{
			// there is no session i.q. this is a preview api call
			return $label;
		}

		if ($label === '%partner%')
		{

			$stepgroup = $this->getContext()->getStepgroup();
			if ($stepgroup['grouping'] == 'inactive')
			{
				$this->getContext()->getApi('log')->error('Trying to access group in a stepgroup without grouping');
				return null;
			}

			$groupApi = $this->getContext()->getApi('group');
			$groupMembers = $groupApi->getGroupMemberLabels('%current%');
			if (sizeof($groupMembers) != 2) {
				throw new Exception('Can not use partner label for groups with more or less than 2 participants');
			}

			$currentParticipant = $this->translateLabel('%current%');
			foreach ($groupMembers as $groupMember) {
				if ($currentParticipant != $groupMember) {
					return $groupMember;
				}
			}
			throw new Exception('No other participant found when partner label used');
		} elseif ($label === '%localPartner%') {
			$groupApi = $this->getContext()->getApi('group');

			if (!isset($options) || !isset($options['stepgroupLabel']) || !isset($options['stepgroupLoop'])) {
				throw new Exception('Stepgroup Label and Stepgroup Loop have to be set to refer to localPartner');
			}

			$stepgroup = $this->getContext()->getStepgroup($options['stepgroupLabel']);
			if ($stepgroup['grouping'] == 'inactive')
			{
				$this->getContext()->getApi('log')->error('Trying to access group in a stepgroup without grouping');
				return null;
			}

			// TODO: add an option for participantState = 'new', 'started', 'finished', 'excluded'
			$groupMembers = $groupApi->getGroupMemberLabels('%localGroup%', $options);
			if (sizeof($groupMembers) != 2) {
				throw new Exception('Can not use partner label for groups with more or less than 2 participants');
			}

			$currentParticipant = $this->translateLabel('%current%');
			foreach ($groupMembers as $groupMember) {
				if ($currentParticipant != $groupMember) {
					return $groupMember;
				}
			}
			throw new Exception('No other participant found when partner label used');
		}
		return $label;
	}

	/**
	 * Get an array of labels of participants within the session.
	 *
	 * The method returns an array of labels of all participants within a session. The array can be restricted to participants matching certain criteria by passing an option array with criteria elements to the method.
	 *
	 * @example $participantLabels = $participantApi->getParticipantLabels();
	 * @example $playerALabels = $participantApi->getParticipantLabels(array('typeLabel' => 'a'));
	 *
	 * @param Array $options Array of options. Available filter options: typeLabel, state, stepgroupLabel, stepgroupLoop, stepLabel.
	 * @return Array Array of participant labels
	 */
	public function getParticipantLabels($options = array())
	{
		$sessionId = $this->getContext()->getSessionId();
		if (is_null($sessionId))
		{
			// there is no session i.q. this is a preview api call
			return array($this->getContext()->getParticipantLabel());
		}

		$participants = $this->getParticipants($options);

		$participantLabels = array();
		foreach ($participants as $participant)
		{
			$participantLabels[] = $participant['label'];
		}
		return $participantLabels;
	}

	/**
	 * Get an array of the participants within the session.
	 *
	 * The method returns an array all participants within a session. The array can be restricted to participants matching certain criteria by passing an option array with criteria elements to the method.
	 *
	 * @example $participants = $participantApi->getParticipants();
	 * @example $playerAs = $participantApi->getParticipants(array('typeLabel' => 'a'));
	 *
	 * @param Array $options Array of options. Available filter options: typeLabel, state, stepgroupLabel, stepgroupLoop, stepLabel.
	 * @return Array Array of participants
	 */
	public function getParticipants($options = array())
	{
		$sessionId = $this->getContext()->getSessionId();
		if (is_null($sessionId))
		{
			// there is no session i.q. this is a preview api call
			return array($this->getContext()->getParticipantLabel());
		}

		if (isset($options['typeLabel']))
		{
			$options['typeLabel'] = (array)$options['typeLabel'];
		}

		if (isset($options['state']))
		{
			$options['state'] = (array)$options['state'];
		}
		else
		{
			// do not return excluded by default
			$options['state'] = array('new', 'started', 'finished');
		}

		if (isset($options['stepLabel']))
		{
			if ($step = $this->getContext()->getApi('step')->get($options['stepLabel']))
			{
				$options['stepId'] = $step['id'];
			}
			else
			{
				$this->getContext()->getApi('log')->error('Could not translate step label "' . $options['stepLabel'] . '" for getParticipants / getParticipantLabels.');
				return array();
			}
		}

		// TODO: increase efficiency by including options in query
		$participantRows = $this->getParticipantTable()->fetchAllBySession($sessionId)->toArray();

		// cache step labels by id:
		$stepLabels = array();

		// create result:
		$participants = array();
		foreach ($participantRows as $participant)
		{
			if (isset($options['typeLabel']))
			{
				if (!in_array($participant['typeLabel'], $options['typeLabel']))
				{
					continue;
				}
			}

			if (isset($options['state']))
			{
				if (!in_array($participant['state'], $options['state']))
				{
					continue;
				}
			}

			if (isset($options['stepgroupLabel']) && $participant['stepgroupLabel'] != $options['stepgroupLabel'])
			{
				continue;
			}

			if (isset($options['stepgroupLoop']) && $participant['stepgroupLoop'] != $options['stepgroupLoop'])
			{
				continue;
			}

			if (isset($options['stepId']) && $participant['stepId'] != $options['stepId'])
			{
				continue;
			}

			if (!isset($stepLabels[ $participant['stepId'] ]))
			{
				$stepLabels[ $participant['stepId'] ] = $this->getContext()->getApi('step')->getLabelById($participant['stepId']);
			}
			$participant['stepLabel'] = $stepLabels[ $participant['stepId'] ];

			$participants[] = $participant;
		}

		return $participants;
	}

	/**
	 * Get participant details.
	 *
	 * Returns an array of information for a participant. If no label is passed the details for the current participant is returned.
	 *
	 * @param String $label Participant label or special label to be translated. Defaults to %current%.
	 * @return Array|null Participant details
	 */
	public function get($label = null)
	{
		if (is_null($label) || $label === '%current%')
		{
			return $this->getContext()->getParticipant();
		}

		$sessionId = $this->getContext()->getSessionId();
		if (is_null($sessionId))
		{
			// there is no session i.q. this is a preview api call
			return array();
		}

		$label = $this->translateLabel($label);

		$participant = $this->getParticipantTable()->fetchRowBySessionAndLabel($sessionId, $label);

		if (!is_null($participant))
		{
			$participant = $participant->toArray();
			$participant['stepLabel'] = $this->getContext()->getApi('step')->getLabelById($participant['stepId']);
			return $participant;
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get participant code.
	 *
	 * Returns the unique participant code which is used to login participants. Returns the code of the current participant if no label parameter is passed.
	 *
	 * @param String $label Participant label or special label to be translated. Defaults to %current%.
	 * @return String|null Participant code
	 */
	public function getCode($label = null)
	{
		$participant = $this->get($label);
		if (is_null($participant) || !is_array($participant))
		{
			return null;
		}

		return $participant['code'];
	}

	/**
	 * Set participant step.
	 *
	 * Returns true when setting participant to the given step succeeds, false otherwise.
	 *
	 * @param String $stepLabel The label of the step you want the participant to be set to. No default value.
	 * @param String $stepgroupLoop The loop number you want the participant to be set to. Defaults to %current% for steps within the same stepgroup, 1 otherwise.
	 * @param String $label Participant label or special label to be translated. Defaults to %current%.
	 * @return Boolean True when setting the participant to the step suceeded, false otherwise.
	 */
	public function setStep($stepLabel, $stepgroupLoop = null, $label = '%current%')
	{
		$context = $this->getContext();
		$stepTable = Sophie_Db_Treatment_Step::getInstance();
		$stepgroupTable = Sophie_Db_Treatment_Stepgroup::getInstance();
		$step = $stepTable->fetchByLabel($context->getTreatmentId(), $stepLabel);
		$stepgroup = $stepgroupTable->find($step->stepgroupId)->current();

		if (is_null($stepgroupLoop))
		{
			if ($stepgroup->label == $context->getStepgroupLabel())
			{
				$stepgroupLoop = $context->getStepgroupLoop();
			}
			else
			{
				$stepgroupLoop = 1;
			}
		}

		if ($stepgroupLoop > $stepgroup->loop)
		{
			// TODO: log error
			return false;
		}

		$label = $this->translateLabel($label);

		$sessionId = $context->getSessionId();
		if (is_null($sessionId))
		{
			// there is no session i.e. this is a preview api call
			// degugging console: would have taken the participant to step ...
			return;
		}

		$participant = $this->getParticipantTable()->fetchRowBySessionAndLabel($sessionId, $label);
		if (is_null($participant))
		{
			// TODO: log error
			return false;
		}

		$participant->stepId = $step->id;
		$participant->stepgroupLabel = $stepgroup->label;
		$participant->stepgroupLoop = $stepgroupLoop;

		if ($participant->state == 'new' || $participant->state == 'finished')
		{
			$participant->state = 'started';
		}

		try
		{
			$participant->save();
		}
		catch (Exception $e)
		{
			// TODO: log this
			return false;
		}

		// TODO: log participant taken to step
		// TODO: event participant leaving step
		// TODO: event participant entering step
		return true;
	}

	/**
	 * Set participant state.
	 *
	 * Returns true when setting participant state succeeds, false otherwise.
	 *
	 * @param String $state State too set participant to (new, started, excluded, finished)
	 * @param String $label Participant label or special label to be translated. Defaults to %current%.
	 * @return Boolean True when setting the participant state succeeded, false otherwise.
	 */
	public function setState($state, $label = null)
	{
		if (!in_array($state, array('new', 'started', 'finished', 'excluded')))
		{
			$this->getContext()->getApi('log')->error('Trying to set invalid state for participant: "'. $state . '"');
			return false;
		}

		$participant = $this->get($label);
		if (is_null($participant) || !is_array($participant))
		{
			$this->getContext()->getApi('log')->error('Trying to set state for a participant that does not exist: "'. $label . '"');
			return false;
		}

		$participant = $this->getParticipantTable()->find($participant['id'])->current();
		$participant->state = $state;
		$participant->save();
		return true;
	}

	/**
	 * Set participant state to excluded.
	 *
	 * Returns true when setting participant state to excluded succeeds, false otherwise.
	 *
	 * @param String $label Participant label or special label to be translated. Defaults to %current%.
	 * @return Boolean True when setting the participant state succeeded, false otherwise.
	 */
	public function exclude($label = null)
	{
		return $this->setState('excluded', $label);
	}
}