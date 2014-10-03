<?php
class Sophie_Service_Session
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

	public function generateCode()
	{
		return $this->mnemonic(8, 4); //. rand(1000, 9999);
	}

	private function mnemonic($charLength = 8, $numLength = 0)
	{
		$v = array(
			'a', 'e', 'i', 'o', 'u'
		);
		$c = array(
			'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'm', 'n', 'p', 'r', 's', 't', 'v', 'w', 'z'
		);
		$result = '';
		$length = ceil($charLength / 2);
		for ($i = 0; $i < $length; $i++)
		{
			$result .= $c[array_rand($c)];
			$result .= $v[array_rand($v)];
		}
		$result = substr($result, 0, $charLength);
		if ($numLength)
		{
			$result .= str_pad((int)rand(0, pow(10, $numLength) - 1), $numLength, '0',
	STR_PAD_LEFT);
		}
		return $result;
	}

	// FUNCTIONS
	public function initParticipantsWithCode($sessionId)
	{
		// session
		$session = Sophie_Db_Session :: getInstance()->find($sessionId)->current();
		if (is_null($session))
		{
			throw new Zend_Exception('referenced session does not exists');
			return;
		}

		// session_participant
		$participants = $session->findDependentRowset('Sophie_Db_Session_Participant');
		if (!is_null($participants) && sizeof($participants) > 0)
		{
			throw new Zend_Exception('participants for this session have alread been created');
			return;
		}

		// sessiontype
		// todo: handle no sessiontype?
		$sessiontype = $session->findParentRow('Sophie_Db_Treatment_Sessiontype');
		if (is_null($sessiontype))
		{
			throw new Zend_Exception('referenced sessiontype does not exists');
			return;
		}

		// treatment
		$treatment = $session->findParentRow('Sophie_Db_Treatment');
		if (is_null($treatment))
		{
			throw new Zend_Exception('referenced treatment does not exists');
			return;
		}

		// treatment_group_structure
		$groupStructures = $treatment->findDependentRowset('Sophie_Db_Treatment_Group_Structure');
		if (sizeof($groupStructures) == 0)
		{
			// TODO: handle no group structure? (just create participants without group association?)
			throw new Zend_Exception('no group structure defined');
			return;
		}

		// treatment_type
		$types = $treatment->findDependentRowset('Sophie_Db_Treatment_Type', null, Sophie_Db_Treatment_Type::getInstance()->select()->order('label'));
		if (sizeof($types) == 0)
		{
			throw new Zend_Exception('no types defined');
			return;
		}

		$sessionParticipantModel = Sophie_Db_Session_Participant :: getInstance();
		$sessionGroupModel = Sophie_Db_Session_Group :: getInstance();

		$participantCount = 0;
		$groupCount = 0;
		$typeCount = array ();
		foreach ($types as $type)
		{
			$typeCount[$type->label] = 0;
		}

		$groupStructureModel = Sophie_Db_Treatment_Group_Structure :: getInstance();
		foreach ($groupStructures as $groupStructure)
		{
			$groupStructure = $groupStructureModel->fetchDisassembledRow($groupStructure->treatmentId, $groupStructure->label);

			// TODO: get groupNumber per groupStructure
			$groupStructureGroupNumber = $sessiontype->size;

			for ($groupStructureGroupCount = 1; $groupStructureGroupCount <= $groupStructureGroupNumber; $groupStructureGroupCount++)
			{
				$groupCount++;

				// create group
				$sessionGroup = array ();
				$sessionGroup['sessionId'] = $session->id;
				$sessionGroup['label'] = $groupStructure['label'] . '.' . $groupStructureGroupCount;
				$sessionGroup['number'] = $groupCount;
				$sessionGroup['groupStructure'] = $groupStructure['label'];
				$sessionGroupModel->insert($sessionGroup);

				foreach ($groupStructure['structure'] as $groupStructureType => $groupStructureTypeDefinition)
				{
					// TODO: handle min != max

					$groupStructureTypeNumber = $groupStructureTypeDefinition['min'];
					for ($groupStructureTypeCount = 1; $groupStructureTypeCount <= $groupStructureTypeNumber; $groupStructureTypeCount++)
					{

						$participantCount++;
						$typeCount[$groupStructureType]++;

						// create participants
						do
						{
							$code = $this->generateCode();
						}
						while (!$sessionParticipantModel->checkUniqueCode($code));

						$sessionParticipantModel->insert(array (
							'sessionId' => $session->id,
							'label' => $groupStructureType . '.' . $typeCount[$groupStructureType],
							'number' => $participantCount,
							'code' => $code,
							'typeLabel' => $groupStructureType
						));
					}
				}

			}
		}
	}

	public function addParticipants($sessionId, $addParticipantsNumber = 1,$participantType = NULL)
	{
		// session
		$session = Sophie_Db_Session :: getInstance()->find($sessionId)->current();
		if (is_null($session))
		{
			throw new Zend_Exception('referenced session does not exists');
			return;
		}

		// treatment
		$treatment = $session->findParentRow('Sophie_Db_Treatment');
		if (is_null($treatment))
		{
			throw new Zend_Exception('referenced treatment does not exists');
			return;
		}

		// treatment_group_structure
		$groupStructures = $treatment->findDependentRowset('Sophie_Db_Treatment_Group_Structure');
		if (sizeof($groupStructures) == 0)
		{
			// TODO: handle no group structure? (just create participants without group association?)
			throw new Zend_Exception('no group structure defined');
			return;
		}

		// treatment_type
		$types = $treatment->findDependentRowset('Sophie_Db_Treatment_Type', null, Sophie_Db_Treatment_Type::getInstance()->select()->order('label'));
		if (sizeof($types) == 0)
		{
			throw new Zend_Exception('no types defined');
			return;
		}

		$sessionParticipantModel = Sophie_Db_Session_Participant :: getInstance();
		$sessionGroupModel = Sophie_Db_Session_Group :: getInstance();
		$existingParticipants = $session->findDependentRowset('Sophie_Db_Session_Participant');
		$existingGroups = $session->findDependentRowset('Sophie_Db_Session_Group');

		$participantCount = sizeof($existingParticipants);
		$groupCount = sizeof($existingGroups);
		
		$typeCount = array ();
		$typeQuote = array();
		foreach ($types as $type)
		{
			$typeCount[$type->label] = 0;
			$typeQuote[$type->label] = 0;
		}

		foreach ($existingParticipants as $exisingParticipant)
		{
			$typeCount[$exisingParticipant->typeLabel]++;
		}

		$groupStructureModel = Sophie_Db_Treatment_Group_Structure :: getInstance();
		foreach ($groupStructures as $groupStructure)
		{
			$groupStructure = $groupStructureModel->fetchDisassembledRow($groupStructure->treatmentId, $groupStructure->label);

			foreach ($groupStructure['structure'] as $groupStructureType => $groupStructureTypeDefinition)
			{
				$typeQuote[$groupStructureType] += $groupStructureTypeDefinition['min'];
			}
		}

		$participantNumberOverAllGroupStructures = 0;
		foreach ($types as $type)
		{
			$participantNumberOverAllGroupStructures += $typeQuote[$type->label];
		}
		$fillGroupNumbers = ceil($participantCount / $participantNumberOverAllGroupStructures);
		if ($participantCount % $participantNumberOverAllGroupStructures == 0)
		{
			$fillGroupNumbers++;
		}
		
		for ($participantsAdded = 0; $participantsAdded < $addParticipantsNumber; $participantsAdded ++)
		{
			// determine participant type
			$newParticipantLabel = null;
			foreach ($types as $type)
			{
				if ($typeCount[$type->label] < ($typeQuote[$type->label] * $fillGroupNumbers))
				{
					$newParticipantLabel = $type->label;
					break;
				}
			}
			
			if (is_null($newParticipantLabel))
			{
				throw new Exception('Cannot find a participant type to fill up');
			}
			
			$participantCount++;
			$typeCount[$newParticipantLabel]++;

			// create participants
			do
			{
				$code = $this->generateCode();
			}
			while (!$sessionParticipantModel->checkUniqueCode($code));

			$sessionParticipantModel->insert(array (
				'sessionId' => $session->id,
				'label' => $newParticipantLabel . '.' . $typeCount[$newParticipantLabel],
				'number' => $participantCount,
				'code' => $code,
				'typeLabel' => $newParticipantLabel
			));
		}
	}

	public function addGroups($sessionId, $groupNumber = 1, $groupStructureLabel = 'G')
	{
		// session
		$session = Sophie_Db_Session :: getInstance()->find($sessionId)->current();
		if (is_null($session))
		{
			throw new Zend_Exception('referenced session does not exists');
			return;
		}

		// treatment
		$treatment = $session->findParentRow('Sophie_Db_Treatment');
		if (is_null($treatment))
		{
			throw new Zend_Exception('referenced treatment does not exists');
			return;
		}

		// treatment_group_structure
		$groupStructures = $treatment->findDependentRowset('Sophie_Db_Treatment_Group_Structure');
		if (sizeof($groupStructures) == 0)
		{
			// TODO: handle no group structure? (just create participants without group association?)
			throw new Zend_Exception('no group structure defined');
			return;
		}

		// treatment_type
		$types = $treatment->findDependentRowset('Sophie_Db_Treatment_Type', null, Sophie_Db_Treatment_Type::getInstance()->select()->order('label'));
		if (sizeof($types) == 0)
		{
			throw new Zend_Exception('no types defined');
			return;
		}

		$sessionParticipantModel = Sophie_Db_Session_Participant :: getInstance();
		$sessionGroupModel = Sophie_Db_Session_Group :: getInstance();
		$existingParticipants = $session->findDependentRowset('Sophie_Db_Session_Participant');
		$existingGroups = $session->findDependentRowset('Sophie_Db_Session_Group');

		$participantCount = sizeof($existingParticipants);
		$groupCount = sizeof($existingGroups);

		$typeCount = array ();
		
		foreach ($types as $type)
		{
			$typeCount[$type->label] = 0;
		}
		foreach ($existingParticipants as $exisingParticipant)
		{
			$typeCount[$exisingParticipant->typeLabel]++;
		}

		$groupStructureModel = Sophie_Db_Treatment_Group_Structure :: getInstance();
		foreach ($groupStructures as $groupStructure)
		{
			if ($groupStructure->label != $groupStructureLabel)
			{
				continue;
			}
			
			$groupStructure = $groupStructureModel->fetchDisassembledRow($groupStructure->treatmentId, $groupStructure->label);

			// TODO: get groupNumber per groupStructure
			$groupStructureGroupNumber = $groupNumber;

			for ($groupStructureGroupCount = 1; $groupStructureGroupCount <= $groupStructureGroupNumber; $groupStructureGroupCount++)
			{
				$groupCount++;

				// create group
				$sessionGroup = array ();
				$sessionGroup['sessionId'] = $session->id;
				$sessionGroup['label'] = $groupStructure['label'] . '.' . $groupCount;
				$sessionGroup['number'] = $groupCount;
				$sessionGroup['groupStructure'] = $groupStructure['label'];
				$sessionGroupModel->insert($sessionGroup);

				foreach ($groupStructure['structure'] as $groupStructureType => $groupStructureTypeDefinition)
				{
					// TODO: handle min != max

					$groupStructureTypeNumber = $groupStructureTypeDefinition['min'];
					for ($groupStructureTypeCount = 1; $groupStructureTypeCount <= $groupStructureTypeNumber; $groupStructureTypeCount++)
					{

						$participantCount++;
						$typeCount[$groupStructureType]++;

						// create participants
						do
						{
							$code = $this->generateCode();
						}
						while (!$sessionParticipantModel->checkUniqueCode($code));

						/*print_r(array (
							'sessionId' => $session->id,
							'label' => $groupStructureType . '.' . $typeCount[$groupStructureType],
							'number' => $participantCount,
							'code' => $code,
							'typeLabel' => $groupStructureType
						));*/
						$sessionParticipantModel->insert(array (
							'sessionId' => $session->id,
							'label' => $groupStructureType . '.' . $typeCount[$groupStructureType],
							'number' => $participantCount,
							'code' => $code,
							'typeLabel' => $groupStructureType
						));
					}
				}

			}
		}
	}

	public function initStaticGrouping($sessionId)
	{
		// session
		$session = Sophie_Db_Session :: getInstance()->find($sessionId)->current();
		if (is_null($session))
		{
			throw new Zend_Exception('referenced session does not exists');
			return;
		}

		// sessiontype
		$sessiontype = $session->findParentRow('Sophie_Db_Treatment_Sessiontype');
		if (is_null($sessiontype))
		{
			throw new Zend_Exception('referenced sessiontype does not exists');
			return;
		}

		$participantGroupModel = Sophie_Db_Session_Participant_Group :: getInstance();

		$groupDefinition = json_decode($sessiontype->groupDefinitionJson, true);
		foreach ($groupDefinition as $sectionKey => $sectionDefinition)
		{
			//echo $sectionKey;
			list($stepgroupLabel, $stepgroupLoop) = explode('.', $sectionKey, 2);

			foreach ($sectionDefinition as $groupKey => $groupDefinition)
			{
				foreach ($groupDefinition as $userDefinition)
				{
					$participantGroupData = array();
					$participantGroupData['sessionId'] = $sessionId;
					$participantGroupData['stepgroupLabel'] = $stepgroupLabel;
					$participantGroupData['stepgroupLoop'] = $stepgroupLoop;
					$participantGroupData['participantLabel'] = $userDefinition;
					$participantGroupData['groupLabel'] = $groupKey;
					$participantGroupModel->insert($participantGroupData);
				}
			}
		}
	}
}