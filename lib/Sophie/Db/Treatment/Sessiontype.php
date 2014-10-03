<?php
class Sophie_Db_Treatment_Sessiontype extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_treatment_sessiontype';
	public $_referenceMap    = array(
				'Treatment' => array(
            		'columns'           => array('treatmentId'),
            		'refTableClass'     => 'Sophie_Db_Treatment',
            		'refColumns'        => array('id')
				));
	
	public $groupStructureLabel = 'G';
	public $lastGroupDefinitionError = '';
	public $groupDefinitionCanBeRepaired = true;

	public function insert(array $data)
	{
		$this->assembleData($data, true);
		return parent::insert($data);
	}

	public function update(array $data, $where)
	{
		$this->assembleData($data, false);
		return parent::update($data, $where);
	}
	
	private function createDefaultSGLGrouping($size, $groupStructure)
	{
		$participants = array();
		$result = array();
		for ($j = 1; $j <= $size; $j++)
		{
			$keyParticipantGroup = $this->groupStructureLabel . '.' . $j;
			$result[$keyParticipantGroup] = array();
			foreach ($groupStructure['structure'] as $typeLabel => $struc)
			{
				for ($k = 1; $k <= $struc['min']; $k++)
				{
					if (!isset($participants[$typeLabel]))
					{
						$participants[$typeLabel] = 0;
					}
					$participants[$typeLabel]++;
					$result[$keyParticipantGroup][] = $typeLabel . '.' . $participants[$typeLabel];
				}
			}
		}
		return $result;
	}

	private function assembleData(&$data, $createDefault = false)
	{
		if (!isset($data['participantMgmt']) || $data['participantMgmt'] == '')
		{
			$data['participantMgmt'] = 'static';
		}

		if (isset($data['groupStructureLabel']))
		{
			$groupStructureLabel = $data['groupStructureLabel'];
			unset($data['groupStructureLabel']);
		}
		else
		{
			$groupStructureLabel = $this->groupStructureLabel;
		}

		if ($data['participantMgmt'] != 'static')
		{
			$data['groupDefinitionJson'] = '';
			return;
		}

		if (isset($data['groupDefinitionJson']))
		{
			$tmp = json_decode($data['groupDefinitionJson'], true);
			$data['groupDefinitionJson'] = json_encode($tmp);
		}

		elseif (isset($data['groupDefinition']) && (is_array($data['groupDefinition']) || is_object($data['groupDefinition'])))
		{
			$data['groupDefinitionJson'] = json_encode($data['groupDefinition']);
			unset($data['groupDefinition']);
		}

		elseif($createDefault && isset($data['treatmentId']) && isset($data['size']))
		{
			$structure = Sophie_Db_Treatment_Group_Structure :: getInstance();
			$groupStructure = $structure->fetchDisassembledRow($data['treatmentId'], $groupStructureLabel);

			$stepgroups = $this->getStepgroups($data['treatmentId']);
			if (!is_array($stepgroups))
			{
				return;
			}
			
			$defaultGrouping = $this->createDefaultSGLGrouping($data['size'], $groupStructure);

			$groupDefinition = array();
			foreach ($stepgroups as $stepgroup)
			{
				if ($stepgroup['grouping'] == 'static' && $stepgroup['loop'] != -1)
				{
					for ($i = 1; $i <= $stepgroup['loop']; $i++)
					{
						$keyStepgroup = $stepgroup['label'] . '.' . $i;
						$groupDefinition[$keyStepgroup] = $defaultGrouping;
					}
				}
			}
			$data['groupDefinitionJson'] = json_encode($groupDefinition);
		}
	}
	
	public function resetGroupDefinition($sessiontypeId)
	{
		$select = $this->select();
		$select->where('id = ?', $sessiontypeId);
		$row = $this->fetchRow($select);
		if (is_null($row))
		{
			return false;
		}
		// disassemble json data
		$data = $row->toArray();

		unset($data['groupDefinitionJson']);
		$this->assembleData($data, true);
		
		$row->groupDefinitionJson = $data['groupDefinitionJson'];
		$row->save();
		return true;
	}
	
	public function repairGroupDefinition($sessiontypeId)
	{
		// prepare data:
		// currently used grouping (to be repaired):
		$disassembledRow = $this->fetchDisassembledRow($sessiontypeId);

		// current data:
		$select = $this->select();
		$select->where('id = ?', $sessiontypeId);
		$sessiontype = $this->fetchRow($select);
		$sessiontype = $sessiontype->toArray();
		$stepgroups = $this->getStepgroups($sessiontype['treatmentId']);
		
		$positions = array();
		// analyse "old" = group definition:
		$pos = 0;
		foreach ($disassembledRow['groupDefinition'] as $sg => $grps)
		{
			$pos++;
			$positions[$sg] = array(
				'old' => $pos,
				'new' => -1
			);
		}
		// analyse "new" = current data's definition:
		$pos = 0;
		foreach ($stepgroups as $stepgroup)
		{
			if ($stepgroup['grouping'] == 'static' && $stepgroup['loop'] != -1)
			{
				for ($i = 1; $i <= $stepgroup['loop']; $i++)
				{
					$pos++;
					$sg = $stepgroup['label'] . '.' . $i;
					if (isset($positions[$sg]))
					{
						$positions[$sg]['new'] = $pos;
					}
					else
					{
						$positions[$sg] = array(
							'old' => -1,
							'new' => $pos
						);
					}
				}
			}
		}
		
		// create new grouping:
		// some preparation:
		$Structure = Sophie_Db_Treatment_Group_Structure :: getInstance();
		$groupStructure = $Structure->fetchDisassembledRow($sessiontype['treatmentId'], $this->groupStructureLabel);

		$defaultGrouping = $this->createDefaultSGLGrouping($sessiontype['size'], $groupStructure);
		
		// this will contain the new grouping:
		$groupDefinition = array();
		// go:
		foreach ($stepgroups as $stepgroup)
		{
			if ($stepgroup['grouping'] == 'static')
			{
				for ($i = 1; $i <= $stepgroup['loop']; $i++)
				{
					$sg = $stepgroup['label'] . '.' . $i;
					if ($positions[$sg]['old'] > 0)
					{
						// SGL with same name did exist in old grouping
						$groupDefinition[$sg] = $disassembledRow['groupDefinition'][$sg];
					}
					else
					{
						// default: use default grouping
						$groupDefinition[$sg] = $defaultGrouping;
						// ...but check if there is a old SGL at the same position without a new position:					
						foreach ($positions as $x => $pos)
						{
							if ($pos['old'] == $positions[$sg]['new'] && $pos['new'] <= 0)
							{
								$groupDefinition[$sg] = $disassembledRow['groupDefinition'][$x];
								break;
							}
						}
					}
				}
			}
		}
		// die('<pre>' . '$groupDefinition = ' . print_r($groupDefinition, 1) . '$defaultGrouping = ' . print_r($defaultGrouping, 1) . '$positions = ' . print_r($positions, 1) . '$stepgroups = ' . print_r($stepgroups, 1) . '$disassembledRow = ' . print_r($disassembledRow, 1));
		
		unset($sessiontype['groupDefinitionJson']);
		$sessiontype['groupDefinition'] = $groupDefinition;
		$where = $this->getAdapter()->quoteInto('id = ?', $sessiontypeId);
		$this->update($sessiontype, $where);
		return true;
	}
	
	public function collectGroupsAndParticipants($row, &$groups, &$participants)
	{
		// Collect:
		$groups = array();
		$participants = array();
		foreach ($row['groupDefinition'] as $sg => $grps)
		{
			foreach ($grps as $pg => $ptps)
			{
				$groups[$pg] = $pg;
				foreach ($ptps as $p)
				{
					$participants[$p] = $p;
				}
			}
		}
		// Get Groupstructure Details
		foreach ($groups as $gLabel => &$group)
		{
			if (!preg_match('/^([a-zA-Z0-9_]+)\.(\d+)$/', $gLabel, $m))
			{
				throw new Exception('Invalid Group Label: ' . $pLabel);
				return;
			}
			$groupstructureLabel = $m[1];
			$no = $m[2];
			$groupstructeResult = Sophie_Db_Treatment_Group_Structure :: getInstance()->fetchDisassembledRow($row['treatmentId'], $groupstructureLabel);
			$group = array(
				'label' => $gLabel,
				'name' => $groupstructeResult['name'],
				'structureLabel' => $groupstructeResult['label'],
				'structureJson' => $groupstructeResult['structureJson']
			);
		}
		
		// Get Participant Details
		$typesResult = Sophie_Db_Treatment_Type :: getInstance()->fetchAllByTreatmentExcludeType($row['treatmentId'], false)->toArray();
		$types = array();
		foreach ($typesResult as $t)
		{
			$types[$t['label']] = $t;
		}
		
		uksort($participants, array($this, 'compareLabels'));
		$s = 0;
		foreach ($participants as $pLabel => &$participant)
		{
			if (!preg_match('/^([a-zA-Z0-9_]+)\.(\d+)$/', $pLabel, $m))
			{
				throw new Exception('Invalid Participant Label: ' . $pLabel);
				return;
			}
			$typeLabel = $m[1];
			$no = $m[2];
			if (!isset($types[$typeLabel]))
			{
				throw new Exception('Invalid Participant Type: ' . $typeLabel);
				return;
			}
			$participant = array(
				'label' => $pLabel,
				'sort' => $s++,
				'name' => $types[$typeLabel]['name'],
				'type' => $typeLabel,
				'no' => $no,
				'icon' => $types[$typeLabel]['icon'],
				'hue' => $types[$typeLabel]['hue']
			);
		}
	}

	public function fetchDisassembledRow($sessiontypeId)
	{
		// fetch row
		$select = $this->select();
		$select->where('id = ?', $sessiontypeId);
		$result = $this->fetchRow($select);
		if (is_null($result))
		{
			return null;
		}
		// disassemble json data
		$result = $result->toArray();
		
		if ($result['participantMgmt'] == 'static')
		{
			$result['groupDefinition'] = json_decode($result['groupDefinitionJson'], true);
			$result['groupDefinitionJson'] = $this->indent($result['groupDefinitionJson']);
			// collect groups and participants
			try
			{
				$this->collectGroupsAndParticipants($result, $result['groups'], $result['participants']);
			}
			catch (Exception $e)
			{
				$result['groups'] = false;
				$result['participants'] = false;
			}
		}
		return $result;
	}
	
	public function checkGroupDefinition($sessiontypeId, $groupDefinition = null)
	{
		$result = true;
		
		$this->lastGroupDefinitionError = '';
		$this->groupDefinitionCanBeRepaired = true;
		
		$select = $this->select();
		$select->where('id = ?', $sessiontypeId);
		$sessiontype = $this->fetchRow($select);
		if (is_null($sessiontype))
		{
			$this->lastGroupDefinitionError = 'Invalid sessiontype';
			$this->groupDefinitionCanBeRepaired = false;
			return false;
		}

		if ($sessiontype->participantMgmt != 'static')
		{
			return true;
		}

		$sessiontype = $sessiontype->toArray();

		if (is_null($groupDefinition))
		{
			$disassembledRow = $this->fetchDisassembledRow($sessiontypeId);
			$groupDefinition = $disassembledRow['groupDefinition'];
		}

		$stepgroups = $this->getStepgroups($sessiontype['treatmentId']);
		if (!is_array($stepgroups))
		{
			$this->lastGroupDefinitionError = 'Invalid stepgroups';
			$this->groupDefinitionCanBeRepaired = false;
			return false;
		}
		
		$structure = Sophie_Db_Treatment_Group_Structure :: getInstance();
		$groupStructure = $structure->fetchDisassembledRow($sessiontype['treatmentId'], $this->groupStructureLabel);

		// collect stepgroups from current data:
		// $sequence => <Label>
		$currentStepgroups = array();
		$sequence = 0;
		$stepgroupCount = 0;
		foreach ($stepgroups as $stepgroup)
		{
			if ($stepgroup['grouping'] == 'static' && $stepgroup['loop'] != -1)
			{
				for ($i = 1; $i <= $stepgroup['loop']; $i++)
				{
					$currentStepgroups[$sequence] = $stepgroup['label'] . '.' . $i;
					$sequence++;
				}
				$stepgroupCount += $stepgroup['loop'];
			}
		}
		
		// check if stepgroups match:
		$i = 0;
		foreach ($groupDefinition as $keyStepgroup => $groups)
		{
			if (!isset($currentStepgroups[$i]))
			{
				$this->lastGroupDefinitionError = 'Stepgroup name / number of loops / sequence mismatches.';
				$result = false;
			}
			elseif ($currentStepgroups[$i] != $keyStepgroup)
			{
				$this->lastGroupDefinitionError = 'Stepgroup name / number of loops / sequence mismatches.';
				$result = false;
			}
			$i++;
			$result = $result && $this->checkAndCompareSGL($groups, $groupStructure, $sessiontype);
		}
		
		// check if there are more stepgroups than in the group definition
		if (count($groupDefinition) != $stepgroupCount)
		{
			$this->lastGroupDefinitionError = 'Stepgroup number mismatches.';
			$result = false;
		}
		
		return $result;
	}

	private function checkAndCompareSGL($groups /* from definition */, $groupStructure /* from current data */, $sessiontype /* from current data */)
	{
		// die('<pre>' . print_r($groups, 1) . print_r($groupStructure, 1) . print_r($sessiontype, 1));
		$result = true;
		
		// check if number of groups matches:
		if (count($groups) != $sessiontype['size'])
		{
			$this->lastGroupDefinitionError = 'Number of groups mismatches.';
			$this->groupDefinitionCanBeRepaired = false;
			$result = false;
		}
		
		$participantTypeCountSGL = array();
		
		foreach ($groups as $groupLabel => $group)
		{
			$participantTypeCountG = array();
			// count participants
			foreach ($group as $participantLabel)
			{
				if (!preg_match('/^([a-zA-Z0-9_]+)\.(\d+)$/', $participantLabel, $m))
				{
					$this->lastGroupDefinitionError = 'Invalid participant label.';
					$this->groupDefinitionCanBeRepaired = false;
					$result = false;
					continue;
				}
				$typeLabel = $m[1];
				$no = $m[2];
				// count participants for SGL:
				if (!isset($participantTypeCountSGL[$typeLabel]))
				{
					$participantTypeCountSGL[$typeLabel] = 0;
				}
				$participantTypeCountSGL[$typeLabel]++;
				// count participants for Group:
				if (!isset($participantTypeCountG[$typeLabel]))
				{
					$participantTypeCountG[$typeLabel] = 0;
				}
				$participantTypeCountG[$typeLabel]++;
			}
			// check if participants match per group:
			foreach ($participantTypeCountG as $typeLabel => $cnt)
			{
				if (!isset($groupStructure['structure'][$typeLabel]))
				{
					$this->lastGroupDefinitionError = 'Participant labels mismatch.';
					$this->groupDefinitionCanBeRepaired = false;
					$result = false;
				}
			}
			foreach ($groupStructure['structure'] as $typeLabel => $participant)
			{
				if (!isset($participantTypeCountG[$typeLabel]))
				{
					if ($participant['min'] > 0)
					{
						$this->lastGroupDefinitionError = 'Number of participants mismatches.';
						$this->groupDefinitionCanBeRepaired = false;
						$result = false;
					}
					continue;
				}
				if ($participantTypeCountG[$typeLabel] < $participant['min'] || $participantTypeCountG[$typeLabel] > $participant['max'])
				{
					$this->lastGroupDefinitionError = 'Number of participants mismatches.';
					$this->groupDefinitionCanBeRepaired = false;
					$result = false;
				}
			}
		}
		// check if participants match per SGL:
		foreach ($groupStructure['structure'] as $typeLabel => $participant)
		{
			if (!isset($participantTypeCountSGL[$typeLabel]))
			{
				if ($participant['min'] > 0)
				{
					$this->lastGroupDefinitionError = 'Number of participants mismatches.';
					$this->groupDefinitionCanBeRepaired = false;
					$result = false;
				}
				continue;
			}
			if ($participantTypeCountSGL[$typeLabel] < $participant['min'] * $sessiontype['size'] || $participantTypeCountSGL[$typeLabel] > $participant['max'] * $sessiontype['size'])
			{
				$this->lastGroupDefinitionError = 'Number of participants mismatches.';
				$this->groupDefinitionCanBeRepaired = false;
				$result = false;
			}
		}
		return $result;
	}
	
	// http://recursive-design.com/blog/2008/03/11/format-json-with-php/
	/**
	 * Indents a flat JSON string to make it more human-readable.
	 *
	 * @param string $json The original JSON string to process.
	 *
	 * @return string Indented version of the original JSON string.
	 */
	private function indent($json)
	{

		$result      = '';
		$pos         = 0;
		$strLen      = strlen($json);
		$indentStr   = "\t";
		$newLine     = "\n";
		$prevChar    = '';
		$outOfQuotes = true;

		for ($i=0; $i<=$strLen; $i++) {

			// Grab the next character in the string.
			$char = substr($json, $i, 1);

			// Are we inside a quoted string?
			if ($char == '"' && $prevChar != '\\') {
				$outOfQuotes = !$outOfQuotes;

			// If this character is the end of an element,
			// output a new line and indent the next line.
			} else if(($char == '}' || $char == ']') && $outOfQuotes) {
				$result .= $newLine;
				$pos --;
				for ($j=0; $j<$pos; $j++) {
					$result .= $indentStr;
				}
			}

			// Add the character to the result string.
			$result .= $char;

			// If the last character was the beginning of an element,
			// output a new line and indent the next line.
			if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
				$result .= $newLine;
				if ($char == '{' || $char == '[') {
					$pos ++;
				}

				for ($j = 0; $j < $pos; $j++) {
					$result .= $indentStr;
				}
			}

			$prevChar = $char;
		}

		return $result;
	}
	
	private function compareLabels($a, $b)
	{
		if (preg_match('/^([a-zA-Z0-9_]+)\.(\d+)$/', $a, $ma) && preg_match('/^([a-zA-Z0-9_]+)\.(\d+)$/', $b, $mb))
		{
			$res = strcasecmp($ma[1], $mb[1]);
			if ($res == 0)
			{
				return $ma[2] - $mb[2];
			}
			return $res;
		}
		else
		{
			return strcasecmp($a, $b);
		}
	}

	
	private function getStepgroups($treatmentId)
	{
		$treatment = Sophie_Db_Treatment :: getInstance()->find($treatmentId)->current();
		if (is_null($treatment))
		{
			throw new Exception('Unknown treatment.');
			return null;
		}

		return $treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup', null, Sophie_Db_Treatment_Stepgroup :: getInstance()->select()->order('position'))->toArray();
	}
}