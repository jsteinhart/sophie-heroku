<?php
class Sophie_Service_Treatment
{
	// SINGLETON
	static protected $_instance = null;
	static public function getInstance()
	{
		if (null === self::$_instance) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	public function fromArray($experimentId, $def)
	{

		switch ($def['header']['formatVersion'])
		{
			case '1.0.0':
			case '1.0.1':
			case '1.0.2':
			case '1.0.3':
			case '1.0.4':
			case '1.0.5':
			case '1.0.6':
			    if (isset($def['content']['sophie_treatment']['layout']))
				{
					unset($def['content']['sophie_treatment']['layout']);
				}
				if (isset($def['content']['sophie_treatment_sessiontype']))
				{
					foreach ($def['content']['sophie_treatment_sessiontype'] as &$sessiontype)
					{
						if (isset($sessiontype['variableDefinition']))
						{
							unset($sessiontype['variableDefinition']);
						}
						if (isset($sessiontype['style']))
						{
							$sessiontype['participantMgmt'] = $sessiontype['style'];
							unset($sessiontype['style']);
						}
					}
				}
				break;

			case '1.0.7':
			case '1.0.8':
				break;
			default:
				throw new Exception('Import format version not supported');
				break;
		}

		try
		{

			// RESTORE TREATMENT
			$treatment = $def['content']['sophie_treatment'];
			$treatment['experimentId'] = $experimentId;
			if (isset($treatment['id'])) unset($treatment['id']);

			$treatmentTable = Sophie_Db_Treatment::getInstance();
			$db = $treatmentTable->getAdapter();
			$db->beginTransaction();

			$treatmentRow = $treatmentTable->createRow();
			$treatmentRow->setFromArray($treatment);
			$treatmentRow->save();
			$treatmentId = $treatmentRow->id;

			// add custom screens
			$screens = Sophie_Db_Treatment_Screen :: getInstance()->find($treatmentId)->current();
			if (isset($def['content']['sophie_treatment_screens']) && is_array($def['content']['sophie_treatment_screens']))
			{
				$def['content']['sophie_treatment_screens']['treatmentId'] = $treatmentId;
				Sophie_Db_Treatment_Screen :: getInstance()->replace($def['content']['sophie_treatment_screens']);
			}

			// add treatment eav
			if (isset($def['content']['sophie_treatment_eav']) && is_array($def['content']['sophie_treatment_eav']))
			{
				$treatmentEav = Sophie_Db_Treatment_Eav :: getInstance();
				foreach ($def['content']['sophie_treatment_eav'] as $name => $value)
				{
					$treatmentEav->replace(array(
						'treatmentId' => $treatmentId,
						'name' => $name,
						'value' => $value,
					));
				}
			}

			// add group structure
			$group_structures = $def['content']['sophie_treatment_group_structure'];
			foreach ($group_structures as $group_structure)
			{
				$oldGroupStructureId = $group_structure['id'];
				unset($group_structure['id']);
				$group_structure['structure'] = Zend_Json::decode($group_structure['structureJson']);
				unset($group_structure['structureJson']);
				$group_structure['treatmentId'] = $treatmentId;
				$groupStructureIdTransition[$oldGroupStructureId] = Sophie_Db_Treatment_Group_Structure::getInstance()->insert($group_structure);
			}

			// add parameter
			$parameters = $def['content']['sophie_treatment_parameter'];
			foreach ($parameters as $parameter)
			{
				unset($parameter['id']);
				$parameter['treatmentId'] = $treatmentId;
				Sophie_Db_Treatment_Parameter::getInstance()->insert($parameter);
			}

			// add sessiontypes
			$sessiontypes = $def['content']['sophie_treatment_sessiontype'];
			foreach ($sessiontypes as $sessiontype)
			{
				$parameters = (isset($sessiontype['sophie_treatment_sessiontype_parameter']))
					? $sessiontype['sophie_treatment_sessiontype_parameter']
					: array();
				$variables = (isset($sessiontype['sophie_treatment_sessiontype_variable']))
					? $sessiontype['sophie_treatment_sessiontype_variable']
					: array();

				unset($sessiontype['sophie_treatment_sessiontype_parameter']);
				unset($sessiontype['sophie_treatment_sessiontype_variable']);
				unset($sessiontype['id']);
				$sessiontype['treatmentId'] = $treatmentId;
				$sessiontypeId = Sophie_Db_Treatment_Sessiontype::getInstance()->insert($sessiontype);

				// add sessiontype parameters
				foreach ($parameters as $parameter)
				{
					$parameter['sessiontypeId'] = $sessiontypeId;
					Sophie_Db_Treatment_Sessiontype_Parameter::getInstance()->insert($parameter);
				}

				// add sessiontype variables
				foreach ($variables as $variable)
				{
					unset($variable['id']);
					$variable['sessiontypeId'] = $sessiontypeId;
					Sophie_Db_Treatment_Sessiontype_Variable::getInstance()->insert($variable);
				}
			}

			// add logs?!

			// add types
			$types = $def['content']['sophie_treatment_type'];
			foreach ($types as $type)
			{
				$type['treatmentId'] = $treatmentId;
				Sophie_Db_Treatment_Type::getInstance()->insert($type);
			}

			// RESTORE TREATMENT STEPGROUPS
			$stepgroups = $def['content']['sophie_treatment_stepgroup'];
			foreach ($stepgroups as $stepgroup)
			{
				if (isset($stepgroup['sophie_treatment_step']))
				{
					$steps = $stepgroup['sophie_treatment_step'];
				}
				else
				{
					$steps = array();
				}

				unset($stepgroup['sophie_treatment_step']);
				unset($stepgroup['id']);

				$stepgroup['treatmentId'] = $treatmentId;
				$stepgroupId = Sophie_Db_Treatment_Stepgroup::getInstance()->insert($stepgroup);

				// RESTORE TREATMENT STEPS
				foreach ($steps as $step)
				{

					$types = $step['sophie_treatment_step_type'];
					$eavs = $step['sophie_treatment_step_eav'];
					unset($step['sophie_treatment_step_type']);
					unset($step['sophie_treatment_step_eav']);
					unset($step['id']);

					$step['stepgroupId'] = $stepgroupId;
					$stepId = Sophie_Db_Treatment_Step::getInstance()->insert($step);

					// RESTORE TREATMENT STEP TYPES
					$stepTypes = array();
					foreach ($types as $type)
					{
						$stepTypes[] = $type['typeLabel'];
					}
					Sophie_Db_Treatment_Step_Type::getInstance()->setByStep($stepId, $stepTypes);

					// RESTORE TREATMENT STEP EAV
					$stepEavs = array();
					foreach ($eavs as $eav)
					{
						unset($eav['id']);
						$eav['stepId'] = $stepId;
						Sophie_Db_Treatment_Step_Eav::getInstance()->insert($eav);
					}
				}
			}

			// add variables
			if(isset($def['content']['sophie_treatment_variable']))
			{
				$variables = $def['content']['sophie_treatment_variable'];
				foreach ($variables as $variable)
				{
					unset($variable['id']);
					$variable['treatmentId'] = $treatmentId;
					Sophie_Db_Treatment_Variable::getInstance()->insert($variable);
				}
			}

			//add assets
			if(isset($def['content']['sophie_treatment_asset']))
			{
				$assets = $def['content']['sophie_treatment_asset'];
				foreach($assets as $asset)
				{
					unset($asset['id']);
					$asset['treatmentId'] = $treatmentId;
					$asset['data'] = base64_decode($asset['data']);
					Sophie_Db_Treatment_Asset::getInstance()->replace($asset);
				}
			}

			// add reports
			if (isset($def['content']['sophie_treatment_report']))
			{
				$reports = $def['content']['sophie_treatment_report'];
				foreach ($reports as $report)
				{
					unset($report['id']);
					$report['treatmentId'] = $treatmentId;
					Sophie_Db_Treatment_Report::getInstance()->insert($report);
				}
			}

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollBack();
			throw $e;
		}

		return $treatmentId;
	}

	public function toArray($id)
	{
		$treatment = Sophie_Db_Treatment::getInstance()->find($id)->current();
		$experiment = $treatment->findParentRow('Sophie_Db_Experiment');

		$def = array(

			'header' => array(
				'format' => 'treatmentJSON',
				'formatVersion' => '1.0.8',
				'createdDate' => date('Y-m-d'),
				'createdTime' => date('H:i:s'),
				'experimentName' => $experiment->name,
				'name' => $treatment->name
			),

			'content' => array(
			)

		);

		$sophieVersionFile = BASE_PATH . DIRECTORY_SEPARATOR . 'VERSION';
		if (file_exists($sophieVersionFile))
		{
			$def['header']['sophieVersion'] = file_get_contents($sophieVersionFile);
		}
		else
		{
			$def['header']['sophieVersion'] = 'undef';
		}

		$def['content']['sophie_treatment'] = $treatment->toArray();

		// add custom screens
		$screens = Sophie_Db_Treatment_Screen :: getInstance()->find($id)->current();
		if (!is_null($screens))
		{
			$screens = $screens->toArray();
		}
		$def['content']['sophie_treatment_screens'] = $screens;

		// add treatment eav
		$eav = Sophie_Db_Treatment_Eav :: getInstance()->getAll($id);
		$def['content']['sophie_treatment_eav'] = $eav;

		// add stepgroups
		$stepgroups = $treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup');
		$def['content']['sophie_treatment_stepgroup'] = array();
		foreach ($stepgroups as $stepgroup)
		{
			$sophie_treatment_stepgroup = $stepgroup->toArray();
			$steps = $stepgroup->findDependentRowset('Sophie_Db_Treatment_Step');
			foreach ($steps as $step)
			{
				$sophie_treatment_step = $step->toArray();

				$stepTypes = $step->findDependentRowset('Sophie_Db_Treatment_Step_Type');
				$sophie_treatment_step['sophie_treatment_step_type'] = array();
				foreach ($stepTypes as $stepType)
				{
					$sophie_treatment_step['sophie_treatment_step_type'][] = $stepType->toArray();
				}

				$stepEavs = $step->findDependentRowset('Sophie_Db_Treatment_Step_Eav');
				$sophie_treatment_step['sophie_treatment_step_eav'] = array();
				foreach ($stepEavs as $stepEav)
				{
					$sophie_treatment_step['sophie_treatment_step_eav'][] = $stepEav->toArray();
				}

				$sophie_treatment_stepgroup['sophie_treatment_step'][] = $sophie_treatment_step;
			}

			$def['content']['sophie_treatment_stepgroup'][] = $sophie_treatment_stepgroup;
		}

		// add group structure
		$group_structures = $treatment->findDependentRowset('Sophie_Db_Treatment_Group_Structure');
		$def['content']['sophie_treatment_group_structure'] = $group_structures->toArray();

		// add parameter
		$parameters = $treatment->findDependentRowset('Sophie_Db_Treatment_Parameter');
		$def['content']['sophie_treatment_parameter'] = $parameters->toArray();

		// add sessiontype
		$sessiontypes = $treatment->findDependentRowset('Sophie_Db_Treatment_Sessiontype');
		$def['content']['sophie_treatment_sessiontype'] = array();

		foreach ($sessiontypes as $sessiontype)
		{
			$sophie_treatment_sessiontype = $sessiontype->toArray();
			// add sessiontype parameters
			$parameters = $sessiontype->findDependentRowset('Sophie_Db_Treatment_Sessiontype_Parameter');
			$sophie_treatment_sessiontype['sophie_treatment_sessiontype_parameter'] = $parameters->toArray();
			// add sessiontype variables
			$variables = $sessiontype->findDependentRowset('Sophie_Db_Treatment_Sessiontype_Variable');
			$sophie_treatment_sessiontype['sophie_treatment_sessiontype_variable'] = $variables->toArray();

			$def['content']['sophie_treatment_sessiontype'][] = $sophie_treatment_sessiontype;
		}

		// add logs?!

		// add types
		$types = $treatment->findDependentRowset('Sophie_Db_Treatment_Type');
		$def['content']['sophie_treatment_type'] = $types->toArray();

		// add variable
		$variables = $treatment->findDependentRowset('Sophie_Db_Treatment_Variable');
		$def['content']['sophie_treatment_variable'] = $variables->toArray();

		//add assets
		$assets = $treatment->findDependentRowset('Sophie_Db_Treatment_Asset');
		$assets = $assets->toArray();

		$assetArray = array();

		foreach($assets as $asset)
		{
			$asset['data'] = base64_encode($asset['data']);
			$assetArray[] = $asset;
		}
		$def['content']['sophie_treatment_asset'] = $assetArray;

		// add reports
		$reports = $treatment->findDependentRowset('Sophie_Db_Treatment_Report');
		$def['content']['sophie_treatment_report'] = $reports->toArray();

		//add md5
		$def['header']['md5'] = md5(print_r($def['content'],true));

		return $def;
	}
}