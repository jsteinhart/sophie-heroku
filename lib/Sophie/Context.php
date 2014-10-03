<?php
class Sophie_Context
{
	public $controller = null;
	public $view = null;
	public $db = null;
	public $cache = null;

	public $previewMode = false;

	// stable attributes
	public $experiment;
	public $experimentId;
	public $treatment;
	public $treatmentId;
	public $sessiontype;
	public $sessiontypeId;
	public $sessionId;
	
	// changing attributes
	public $participant;
	public $participantLabel;
	public $groupLabel;
	public $session;
	public $stepgroup;
	public $stepgroupId;
	public $stepgroupLabel;
	public $stepgroupLoop;
	public $step;
	protected $stepAttributes;
	public $stepId;
	public $steptype;
	public $apis = array();

	public $processContextLevel = 'step';
	public $personContextLevel = 'participant';

	public function __clone()
	{
		$this->steptype = null;
		$this->apis = array();
	}

	public function refresh()
	{
		$this->steptype = null;
		$this->apis = array();

		if (is_null($this->participant))
		{
			// only update session, steps have to set manually
			
		}
		else
		
		$participant = $this->getParticipantRow();
		if ($participant['stepId'] != $this->stepId)
		{
			$this->step = null;
		}

		if ($participant['stepgroupId'] != $this->stepgroupId)
		{
			$this->stepgroup = null;
			$this->groupLabel = null;
		}

		if ($participant['stepgroupLoop'] != $this->stepgroupLoop)
		{
			$this->groupLabel = null;
		}

		$this->session = null;
		$this->participant = $participant;
	}

	protected function fetchStepAttributeRows($stepId)
	{
		if ($this->processContextLevel != 'step')
		{
			throw new Exception('Step is not available for a ' . $this->processContextLevel . ' context');
		}

		$useCache = $this->getSessionCacheTreatment();
		if ($useCache)
		{
			$cache = $this->getCache();
			$cachePrefix = 'sophie_sessionCache_' . $this->getSessionId() . '_';
			$cacheKey = $cachePrefix . 'treatmentStepAttributes_' . $stepId;
			$this->stepAttributes = $cache->load($cacheKey);

			if ($this->stepAttributes !== false)
			{
				return;
			}
		}

		$db = $this->getDb();
		$this->stepAttributes = (array)$db->fetchPairs('SELECT name, value FROM sophie_treatment_step_eav WHERE stepID = ' . $db->quote($stepId));

		if ($useCache)
		{
			$cache->save($this->stepAttributes, $cacheKey);
		}
	}

	// generate a unique idenifier for frontend
	public function getChecksum()
	{
		return md5($this->getStepgroupLabel() . '-' . $this->getStepgroupLoop() . '-' . $this->getStepId() . '-' . $this->getParticipantLabel());
	}
	
	public function setPreviewMode($previewMode)
	{
		$this->previewMode = ( $previewMode === true );
	}

	public function getPreviewMode()
	{
		return $this->previewMode;
	}

	public function setPersonContextLevel($personContextLevel)
	{
		$this->personContextLevel = $personContextLevel;
	}

	public function getPersonContextLevel()
	{
		return $this->personContextLevel;
	}

	public function setProcessContextLevel($processContextLevel)
	{
		$this->processContextLevel = $processContextLevel;
	}

	public function getProcessContextLevel()
	{
		return $this->processContextLevel;
	}

	public function setSteptype($steptype)
	{
		$this->steptype = $steptype;
	}

	public function getSteptype()
	{
		if (is_null($this->steptype))
		{
			$step = $this->getStep();
			$steptype = \Sophie_Steptype_Factory::getSystemInstance()->get($step['steptypeSystemName']);

			$steptype->setContext($this);
			$this->setSteptype($steptype);
		}
		
		return $this->steptype;
	}

	public function setTreatmentId($treatmentId)
	{
		if ($this->treatmentId != $treatmentId)
		{
			$this->treatmentId = $treatmentId;
			if (!is_null($this->treatment) && $this->treatment['id'] != $treatmentId)
			{
				$this->treatment = null;
			}
		}
	}

	public function getTreatmentId()
	{
		if (is_null($this->treatmentId))
		{
			if (is_null($this->sessionId))
			{
				throw new Exception('Session Id not found when fetching treatmentId');
			}
			else
			{
				$session = $this->getSession();
				$this->setTreatmentId($session['treatmentId']);
			}
		}
		return $this->treatmentId;
	}

	public function setTreatment($treatment)
	{
		$this->treatment = $treatment;
		$this->setTreatmentId($treatment['id']);
	}

	public function getTreatment()
	{
		if (is_null($this->treatment))
		{
			$this->setTreatment($this->getDb()->fetchRow('SELECT * from sophie_treatment WHERE id = ' . $this->getTreatmentId()));
		}
		return $this->treatment;
	}

	public function setStepgroupLabel($stepgroupLabel)
	{
		if ($this->stepgroupLabel != $stepgroupLabel)
		{
			$this->stepgroupLabel = $stepgroupLabel;
			if (!is_null($this->stepgroup) && $this->stepgroup['label'] != $stepgroupLabel)
			{
				$this->stepgroup = null;
			}
		}
	}

	public function getStepgroupLabel()
	{
		if ($this->processContextLevel=='treatment')
		{
			throw new Exception('Process level of context is treatment. Cannot get current StepgroupLabel.');
		}
		return $this->stepgroupLabel;
	}

	public function setStepgroupLoop($stepgroupLoop)
	{
		$this->stepgroupLoop = $stepgroupLoop;
	}

	public function getStepgroupLoop()
	{
		if ($this->processContextLevel=='treatment' || $this->processContextLevel=='stepgroup')
		{
			throw new Exception('Process level of context is ' . $this->processContextLevel . '. Cannot get current StepgroupLoop.');
		}
		return $this->stepgroupLoop;
	}

	public function setStepId($stepId)
	{
		if ($this->stepId != $stepId)
		{
			$this->stepId = $stepId;
			if (!is_null($this->step) && $this->step['id'] != $stepId)
			{
				$this->step = null;
			}
		}
	}

	public function getStepId()
	{
		if ($this->processContextLevel=='treatment' || $this->processContextLevel=='stepgroup' || $this->processContextLevel=='stepgroupLoop')
		{
			throw new Exception('Process level of context is ' . $this->processContextLevel . '. Cannot get current StepgroupLoop.');
		}
		return $this->stepId;
	}

	public function getStepLabel()
	{
		if ($this->processContextLevel=='treatment' || $this->processContextLevel=='stepgroup' || $this->processContextLevel=='stepgroupLoop')
		{
			throw new Exception('Process level of context is ' . $this->processContextLevel . '. Cannot get current StepgroupLoop.');
		}
		$step = $this->getStep();
		return $step['label'];
	}

	public function checkStepgroupRunConditionScript()
	{
		$stepgroup = $this->getStepgroup();

		$runThisStepgroup = true;

		if ($stepgroup['runConditionScript'] != '')
		{
			$scriptSandbox = $context->getScriptSandbox();
			$scriptReturn = $scriptSandbox->run($stepgroup['runConditionScript']);
			if (!is_null($scriptReturn))
			{
				$runThisStepgroup = $scriptReturn;
			}

			if ($runThisStepgroup != 'skipStepgroup' && $runThisStepgroup != 'skipStepgroupLoop')
			{
				$runThisStepgroup = (boolean)$runThisStepgroup;
				
				if ($runThisStepgroup == false)
				{
					$runThisStepgroup = $stepgroup['runConditionFalse'];
				}
			}

			$evalOutput = $scriptSandbox->getEvalOutput();
			$evalOutput = trim($evalOutput);
			if ($evalOutput != '')
			{
				if (strlen($evalOutput) > 100)
				{
					$evalOutputShort = substr($evalOutput, 0, 90) . '...';
				}
				else
				{
					$evalOutputShort = $evalOutput;
					$evalOutput = null;
				}
				Sophie_Db_Session_Log :: log($this->getSessionId(), 'runConditionScript: ' . $evalOutputShort, 'debug', $evalOutput);
			}
			$scriptSandbox->clearEvalOutput();
		}
		return $runThisStepgroup;
	}

	public function getSessionCacheTreatment()
	{
		if ($this->previewMode === true || $this->getSessionId() === null)
		{
			return false;
		}

		$session = $this->getSession();
		return ($session['cacheTreatment'] === '1');
	}

	public function isStepActive()
	{
		$step = $this->getStep();
		return ($step['active'] === '1');
	}

	public function setSessionId($sessionId)
	{
		if ($this->sessionId != $sessionId)
		{
			$this->sessionId = $sessionId;
			if (!is_null($this->session) && $this->session['id'] != $sessionId)
			{
				$this->session = null;
			}
		}
	}

	public function getSessionId()
	{
		return $this->sessionId;
	}

	public function setParticipantLabel($participantLabel)
	{
		if ($this->participantLabel != $participantLabel)
		{
			$this->participantLabel = $participantLabel;
			if (!is_null($this->participant) && $this->participant['label'] != $participantLabel)
			{
				$this->participant = null;
			}
		}
	}

	public function getParticipantLabel()
	{
		if ($this->processContextLevel=='none' || $this->processContextLevel=='group')
		{
			throw new Exception('Person level of context is ' . $this->processContextLevel . '. Cannot get current Participat Label.');
		}
		return $this->participantLabel;
	}

	public function setGroupLabel($groupLabel)
	{
		$personContextLevel = $this->getPersonContextLevel();
		if ($personContextLevel != 'group')
		{
			throw new Exception('Group label should only be set manually for a group person context level');
		}
		$this->groupLabel = $groupLabel;
	}

	public function getGroupLabel()
	{
		$personContextLevel = $this->getPersonContextLevel();
		if ($personContextLevel == 'none')
		{
			throw new Exception('Person level of context is ' . $personContextLevel . '. Cannot get current Group Label.');
		}
		if ($personContextLevel == 'participant')
		{
			return $this->getApi('group')->translateLabel('%current%');
		}
		return $this->groupLabel;
	}

	public function setExperiment($experiment)
	{
		$this->experiment = $experiment;
		$this->setExperimentId($experiment['id']);
	}

	public function getExperiment()
	{
		if (is_null($this->experiment))
		{
			$this->setExperiment($this->getDb()->fetchRow('SELECT * from sophie_experiment WHERE id = ' . $this->getExperimentId()));
		}
		return $this->experiment;
	}

	public function getExperimentId()
	{
		if (is_null($this->experimentId))
		{
			$treatment = $this->getTreatment();
			$this->experimentId = $treatment['experimentId'];
		}
		return $this->experimentId;
	}

	public function setStepgroupId($stepgroupId)
	{
		if ($this->stepgroupId != $stepgroupId)
		{
			$this->stepgroupId = $stepgroupId;
			if (!is_null($this->stepgroup) && $this->stepgroup['id'] != $stepgroupId)
			{
				$this->stepgroup = null;
			}
		}
	}

	public function setStepgroup($stepgroup)
	{
		$this->stepgroup = $stepgroup;
		$this->setTreatmentId($stepgroup['treatmentId']);
		$this->setStepgroupId($stepgroup['id']);
		$this->setStepgroupLabel($stepgroup['label']);
	}

	public function getStepgroup()
	{
		if (is_null($this->stepgroup))
		{
			$this->setStepgroup($this->getDb()->fetchRow('SELECT * from sophie_treatment_stepgroup WHERE id = ' . $this->getStepgroupId()));
		}
		return $this->stepgroup;
	}

	public function getStepgroupId()
	{
		if (is_null($this->stepgroupId))
		{
			$this->stepgroupId = $this->getDb()->fetchOne('SELECT id from sophie_treatment_stepgroup WHERE treatmentId = ' . $this->getTreatmentId() . ' AND label = ' . $this->getDb()->quote($this->getStepgroupLabel()));
		}
		return $this->stepgroupId;
	}

	public function setStep($step)
	{
		$this->step = $step;
		$this->setStepId($step['id']);
		$this->setStepgroupId($step['stepgroupId']);
	}

	public function getStep()
	{
		if (is_null($this->step))
		{
			$this->setStep($this->getDb()->fetchRow('SELECT * from sophie_treatment_step WHERE id = ' . $this->getStepId()));
		}
		return $this->step;
	}

	public function getStepAttribute($attributeName)
	{
		if (is_null($this->stepAttributes))
		{
			$this->fetchStepAttributeRows($this->getStepId());			
		}
		
		if (!isset($this->stepAttributes[$attributeName]))
		{
			return null;
		}

		return $this->stepAttributes[$attributeName];
	}

	public function getStepAttributes()
	{
		if (is_null($this->stepAttributes))
		{
			$this->fetchStepAttributeRows($this->getStepId());			
		}
		return $this->stepAttributes;
	}

	public function setSession($session)
	{
		$this->session = $session;
		$this->setSessionId($session['id']);
		$this->setTreatmentId($session['treatmentId']);
	}

	public function getSessionRow()
	{
		return $this->getDb()->fetchRow('SELECT * from sophie_session WHERE id = ' . $this->getSessionId());
	}

	public function getSession()
	{
		if (is_null($this->session))
		{
			$this->setSession($this->getSessionRow());
		}
		return $this->session;
	}

	public function getSessionState()
	{
		$session = $this->getSession();
		return $session['state'];
	}

	public function setParticipant($participant)
	{
		$this->participant = $participant;
		$this->setSessionId($participant['sessionId']);
		$this->setParticipantLabel($participant['label']);
		$this->setStepgroupLabel($participant['stepgroupLabel']);
		$this->setStepgroupLoop($participant['stepgroupLoop']);
		$this->setStepId($participant['stepId']);
	}

	public function getParticipantRow()
	{
		return $this->getDb()->fetchRow('SELECT * from sophie_session_participant WHERE sessionId = ' . $this->getSessionId() . ' AND label = ' . $this->getDb()->quote($this->getParticipantLabel()));
	}

	public function getParticipant()
	{
		if (is_null($this->participant))
		{
			$this->setParticipant($this->getParticipantRow());
		}
		return $this->participant;
	}

	public function getParticipantTypeLabel()
	{
		$participant = $this->getParticipant();
		return $participant['typeLabel'];
	}

	public function setController(Zend_Controller_Action $controller)
	{
		$this->controller = $controller;
	}

	public function getController()
	{
		return $this->controller;
	}

	public function getView()
	{
		return $this->view;
	}

	public function setView($view)
	{
		$this->view = $view;
	}

	public function getDb()
	{
		if (!is_null($this->db))
		{
			return $this->db;
		}
		return Zend_Registry :: get('db');
	}

	public function setDb($db)
	{
		$this->db = $db;
	}

	public function getCache()
	{
		if (!is_null($this->cache))
		{
			return $this->cache;
		}
		return Zend_Registry :: get('cache');
	}

	public function setCache($cache)
	{
		$this->cache = $cache;
	}

	///////////////////////////////////////////////////////////////
	// FUNCTIONS FOR TRANSACTION SAFETY
	/////////////////////////////////////////////////////////////////

	// TODO: REFRESH CONTEXT = use load context too?
	// TODO: LOAD CONTEXT FROM PARTICIPANT
	// TODO: IS RUNNING

	// VALIDATE CONTEXT
	public function isUpToDate()
	{
		$session = $this->getSession();
		$sessionNow = $this->getSessionRow();
		if ($session['state'] != $sessionNow['state'])
		{
			return false;
		}

		$participant = $this->getParticipant();
		$participantNow = $this->getParticipantRow();

		if ($participant['state'] != $participantNow['state'])
		{
			return false;
		}

		if ($participant['sessionId'] != $participantNow['sessionId'])
		{
			return false;
		}

		if ($participant['id'] != $participantNow['id'])
		{
			return false;
		}

		if ($participant['stepgroupLabel'] != $participantNow['stepgroupLabel'])
		{
			return false;
		}

		if ($participant['stepgroupLoop'] != $participantNow['stepgroupLoop'])
		{
			return false;
		}

		if ($participant['stepId'] != $participantNow['stepId'])
		{
			return false;
		}

		return true;
	}

	// LOCK SESSION/GROUP/PARTICIPANT SEMAPHORE
	// LOCK NAMED SEMAPHORE ?!
	public function beginTransaction()
	{
		$db = $this->getDb();
		$db->beginTransaction();

		// cp. http://dev.mysql.com/doc/refman/5.1/en/innodb-deadlocks.html
		// set update to lock auxiliary “semaphore” table
		$db->query('UPDATE sophie_session SET lastLock = NOW() WHERE id=' . $db->quote($this->getSessionId()));
	}

	public function commitTransaction()
	{
		$db = $this->getDb();
		$db->commit();
	}

	public function rollBackTransaction()
	{
		$db = $this->getDb();
		$db->rollBack();
	}

	private function getLogApi()
	{
		try {
			$logApi = $this->getApi('log', '1.0.0');
		}
		catch (Exception $e)
		{
			echo 'Logging API can not be loaded';
			exit;
		}
		return $logApi;
	}

	public function getApi($name, $version = null, $reset = false)
	{
		// TODO: cache avaliable APIs and API versions

		$singletonName = $name;
		if (is_null($version) || $version === '' || $version === '*')
		{
			$singletonName .= '_last';
		}
		else
		{
			$singletonName .= '_' . $version;
		}

		if (!isset($this->apis[$singletonName]) || is_null($this->apis[$singletonName]) || $reset)
		{
			if (strpos($name, '_'))
			{
				list($classNamespace, $classApiName) = explode('_', $name, 2);

			}
			else
			{
				$classNamespace = 'sophie';
				$classApiName = $name;
			}

			$apiFactory = Sophie_Api_Factory::getSystemInstance();
			$className = ucfirst($classNamespace) . '_Api_' . ucfirst($classApiName);

			if (is_null($version) || $version === '' || $version === '*')
			{
				// TODO: directly use getLastApiVersion
				$version = $apiFactory->findLastApiVersion($className);
				if (is_null($version))
				{
					$logApi = $this->getLogApi();
					$logApi->error('No version of API found: ' . $name);
					return null;
				}
			}

			$versionParts = explode('.', $version);
			if (!sizeof($versionParts) == 3 || !is_numeric($versionParts[0]) || !is_numeric($versionParts[1]) || !is_numeric($versionParts[2]))
			{
				$logApi = $this->getLogApi();
				$logApi->error('Illegal version number format given for API initalization: ' . $version);
				return null;
			}

			$versionParts[0] = (int)$versionParts[0];
			$versionParts[1] = (int)$versionParts[1];
			$versionParts[2] = (int)$versionParts[2];
			$version = implode('_', $versionParts);

			$className .= '_' . $version;

			try
			{
				$this->apis[$singletonName] = $apiFactory->get($className , $this);
			}
			catch (Exception $e)
			{
				$logApi = $this->getLogApi();
				$logApi->error('Loading API ' . $name . ' failed');
			}
		}
		if (!isset($this->apis[$singletonName]))
		{
			return null;
		}
		else
		{
			return $this->apis[$singletonName];
		}
	}

	public function getStdApis()
	{
		$stdApis = array(
			'context' => 'context',
			'variableApi' => 'variable',
			'groupApi' => 'group',
			'participantApi' => 'participant',
			'stepgroupApi' => 'stepgroup',
			'stepApi' => 'step',
			'processApi' => 'process',
			'parameterApi' => 'parameter',
			'assetApi' => 'asset',
			'requestApi' => 'request',
			'api' => 'api',
			// using $request is deprecated
			'request' => 'request',
		);

		$apis = array();
		foreach ($stdApis as $apiKey => $apiName)
		{
			$apis[$apiKey] = $this->getApi($apiName);
		}
		return $apis;
	}

	public function getScriptSandbox()
	{
		$scriptSandbox = new Sophie_Script_Sandbox();
		$scriptSandbox->setContext($this);
		$scriptSandbox->setLocalVars($this->getStdApis());
		return $scriptSandbox;
	}
}
