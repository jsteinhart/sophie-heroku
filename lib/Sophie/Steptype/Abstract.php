<?php
abstract class Sophie_Steptype_Abstract
{
	protected $options = array(
		self :: RUN_CONDITIONS => array(
			self :: ENABLED => true
		),
		self :: TIMER => array(
			self :: ENABLED => true,
			self :: FORCED => false,
		),
		self :: STEPSYNC => array(
			self :: ENABLED => true
		)
	);

	// constants for $options array
	const RUN_CONDITIONS = 'run_conditions';
	const TIMER = 'timer';
	const STEPSYNC = 'stepsync';
	const ENABLED = 'enabled';
	const FORCED = 'forced';

	// constants for timer state
	const TIMER_NOT_RUNNING = 'timer_not_running';
	const TIMER_COUNTDOWN_RUNNING = 'timer_countdown_running';
	const TIMER_RUNNING = 'timer_running';
	const TIMER_EXPIRED = 'timer_expired';

	// variables set by the frontend controller
	public $controller;
	public $context;

	public $frontUrl = '/expfront/step/index';

	// variables holding helpers and APIs
	public $phpSanitizer = null;

	public $locale = null;
	public $translate = null;
	public $translatePaths = array ();

	public $stepUseTransaction = true;
	public $stepRenderer = null;
	public $scriptSandbox = null;

	// variables used for the admin section
	public $_adminForm = null;
	public $adminTabErrors = array ();
	public $adminSteptypeInfo = null;

	private $attributeConfigurations = null;
	private $attributeCacheValues = null;
	private $attributeRuntimeValues = null;

	/*
	 * CONSTRUCTOR AND GETTER/SETTER FUNCTIONS
	 */

	public function __construct()
	{
	}

	public function getBasePath()
	{
		$c = new ReflectionClass($this);
		return dirname($c->getFileName());
	}

	public function setController(Zend_Controller_Action $controller)
	{
		$this->controller = $controller;
	}

	public function getController()
	{
		return $this->controller;
	}

	public function setView(Zend_View_Abstract $view)
	{
		$this->view = $view;
	}

	public function getView()
	{
		return $this->view;
	}

	public function setContext(Sophie_Context $context)
	{
		$this->context = $context;
	}

	public function getContext()
	{
		return $this->context;
	}

	public function getDb()
	{
		return Zend_Registry :: get('db');
	}

	public function setFrontUrl($frontUrl)
	{
		$this->frontUrl = $frontUrl;
	}

	public function getFrontUrl()
	{
		return $this->frontUrl;
	}

	public function getStepRenderer($reset = false)
	{
		if (is_null($this->stepRenderer) || $reset)
		{
			$this->stepRenderer = new Sophie_Render_Php();
			$this->stepRenderer->setSteptype($this);
			//$this->stepRenderer->setController($this->getController());
			$this->stepRenderer->setContext($this->getContext());

			$this->stepRenderer->setLocalVars($this->getContext()->getStdApis());

			$this->stepRenderer->setUseStreamWrapper(true);
		}
		return $this->stepRenderer;
	}

	public function getScriptSandbox($reset = false)
	{
		if (is_null($this->scriptSandbox) || $reset)
		{
			$this->scriptSandbox = $this->getContext()->getScriptSandbox();
		}
		return $this->scriptSandbox;
	}

	// attribute configurations
	protected function getSteptypeAttributeConfigurations()
	{
		$attributeConfigurations = array(
			'runConditionScript' => array(
				'setByApi' => false
			),
			'runConditionDefault' => array(
				'setByApi' => false
			),

			'initializationScript' => array(
				'setByApi' => false
			),

			'layoutAdditionalCSS' => array(
				'setByApi' => false
			),
			'layoutTheme' => array(
				'setByApi' => false
			),
			'internalNote' => array(
				'setByApi' => false
			),
		);

		if ($this->options[ self :: TIMER ][ self :: ENABLED ])
		{
			$attributeConfigurations = array_merge($attributeConfigurations,
				array(
					'timerDuration' => array(
						'group'				=> 'Timer',
						'title'				=> 'Duration',
						'validatorType'		=> 'numeric'
					),
					'timerCountdownDuration' => array(
						'group'				=> 'Timer',
						'title'				=> 'Countdown Duration',
						'validatorType'		=> 'numeric'
					),
					'timerEnabled' => array(
						'group'				=> 'Timer',
						'title'				=> 'Enabled',
						'validatorRegExp'	=> '/(1|0)/'
					),
					'timerOnTimeout' => array(
						'group'				=> 'Timer',
						'title'				=> 'On Timeout'
						//'validatorRegExp'	=> '/(1|0)/'
					),
					'timerDisplay' => array(
						'group'				=> 'Timer',
						'title'				=> 'Display',
						'validatorRegExp'	=> '/(1|0)/'
					),
					'timerContext' => array(
						'group'				=> 'Timer',
						'title'				=> 'Context'
						//'validatorRegExp'	=> '/(1|0)/'
					),
					'timerStart' => array(
						'group'				=> 'Timer',
						'title'				=> 'Start'
						//'validatorRegExp'	=> '/(1|0)/'
					),
					'timerCountdownEnabled' => array(
						'group'				=> 'Timer',
						'title'				=> 'Countdown Enabled',
						'validatorRegExp'	=> '/(1|0)/'
					),
					'timerProceedBeforeTimeout' => array(
						'group'				=> 'Timer',
						'title'				=> 'Proceed before Timeout',
						'validatorRegExp'	=> '/(1|0)/'
					),
					'timerProceedTimeVarname' => array(
						'group'				=> 'Timer',
						'title'				=> 'Proceed Time Variable',
						'validatorType'		=> 'string'
					),
					'timerRemainingTimeVarname' => array(
						'group'				=> 'Timer',
						'title'				=> 'Remaining Time Variable',
						'validatorType'		=> 'string'
					),
					'timerOnTimeoutWarning' => array(
						'group'				=> 'Timer',
						'title'				=> 'Timeout Warning'
					),
					'timerStartupHeadline' => array(
						'group'				=> 'Timer',
						'title'				=> 'Startup Headline'
					),
					'timerStartupBody' => array(
						'group'				=> 'Timer',
						'title'				=> 'Startup Body'
					),
					'timerCountdownHeadline' => array(
						'group'				=> 'Timer',
						'title'				=> 'Countdown Headline'
					),
					'timerCountdownBody' => array(
						'group'				=> 'Timer',
						'title'				=> 'Countdown Body'
					),
					'timerGracePeriodServer' => array(
						'group'				=> 'Timer',
						'title'				=> 'Grace Period Server',
						'validatorType'		=> 'numeric'
					),
					'timerGracePeriodClient' => array(
						'group'				=> 'Timer',
						'title'				=> 'Grace Period Client',
						'validatorType'		=> 'numeric'
					),
				)
			);
		}
		return $attributeConfigurations;
	}

	final public function initAttributeConfigurations()
	{
		$this->attributeConfigurations = $this->getSteptypeAttributeConfigurations();
	}

	final public function getAttributeConfigurations()
	{
		if (is_null($this->attributeConfigurations))
		{
			$this->initAttributeConfigurations();
		}
		return $this->attributeConfigurations;
	}

	final public function getAttributeConfiguration($attributeName)
	{
		if (is_null($this->attributeConfigurations))
		{
			$this->initAttributeConfigurations();
		}

		if (!isset($this->attributeConfigurations[$attributeName]))
		{
			return null;
		}

		return $this->attributeConfigurations;
	}

	public function validateAttributeValueAgainstConfiguration($attributeName, $attributeValue)
	{
		$attributeConfiguration = $this->getAttributeConfiguration($attributeName);

		if (is_null($attributeConfiguration))
		{
			return false;
		}

		if (isset($attributeConfiguration['validatorType']))
		{
			$typeTest = false;
			switch ($attributeConfiguration['validatorType'])
			{
				case 'numeric':
					$typeTest = is_numeric($attributeValue);
					break;
				case 'scalar':
					$typeTest = is_scalar($attributeValue);
					break;
				case 'int':
				case 'integer':
				case 'long':
					$typeTest = is_int($attributeValue);
					break;
				case 'float':
				case 'double':
				case 'real':
					$typeTest = is_float($attributeValue);
					break;
				case 'bool':
					$typeTest = is_bool($attributeValue);
					break;
				case 'string':
					$typeTest = is_string($attributeValue);
					break;
				case 'context':
					$typeTest = in_array($attributeValue, array('EE', 'ES', 'ESL', 'GE', 'GS', 'GSL', 'PE', 'PS', 'PSL'));
					break;
				case 'json':
					$typeTest = is_string($attributeValue) && (json_decode($attributeValue) !== null);
					break;
				default:
					// TODO: return false + message and let parent function handle this?
					trigger_error('Could not set attribute: Attribute type validator unknown.', E_USER_WARNING);
					return false;
			}

			if (!$typeTest)
			{
				// TODO: return false + message and let parent function handle this?
				trigger_error('Could not set attribute: Attribute value invalid. Must be of type "' . $attributeConfiguration['validatorType'] . '".', E_USER_WARNING);
				return false;
			}
		}

		if (isset($attributeConfiguration['validatorRegExp']) && !preg_match($attributeConfiguration['validatorRegExp'], $attributeValue))
		{
			// TODO: return false + message and let parent function handle this?
			trigger_error('Could not set attribute: Attribute value invalid.', E_USER_WARNING);
			return false;
		}

		return true;
	}


	// step attributes (DO NOT USE THESE FUNCTIONS IN THE FRONTEND: INSTEAD USE $context->getStepAttribute(..) or ->getStepAttributes()
	public function getAttributeModel()
	{
		return Sophie_Db_Treatment_Step_Eav :: getInstance();
	}

	public function initAttributeCacheValues()
	{
		$useCache = $this->getContext()->getSessionCacheTreatment();

		if ($useCache)
		{
			$cacheId = 'sophieTreatmentStepEav_' . $this->getContext()->getStepId();
			$cache = $this->getContext()->getCache();

			$this->attributeCacheValues = $cache->load('sophieTreatmentStepEav_' . $this->getContext()->getStepId());

			if ($this->attributeCacheValues !== false)
			{
				return;
			}
		}

		$db = $this->getDb();
		$this->attributeCacheValues = $db->fetchPairs('SELECT name, value FROM sophie_treatment_step_eav WHERE stepID = ' . $db->quote($this->getContext()->getStepId()));

		if ($useCache)
		{
			$cache->save($this->attributeCacheValues, $cacheId);
		}
	}

	public function getAttributeValues($forceReload = false)
	{
		if (is_null($this->attributeCacheValues) || $forceReload)
		{
			$this->initAttributeCacheValues();
		}
		return $this->attributeCacheValues;
	}

	public function getAttributeValue($attributeName, $forceReload = false, $forceReloadAll = false)
	{
		if (is_null($this->attributeCacheValues))
		{
			$this->initAttributeCacheValues();
		}
		elseif ($forceReload)
		{
			if ($forceReloadAll)
			{
				$this->initAttributeCacheValues();
			}
			else
			{
				$db = $this->getDb();
				$this->attributeCacheValues[$attributeName] = $db->fetchOne('SELECT value FROM sophie_treatment_step_eav WHERE stepId = ' . $this->getContext()->getStepId() . ' AND name=' . $db->quote($attributeName));
			}
		}

		if (array_key_exists($attributeName, $this->attributeCacheValues))
		{
			return $this->attributeCacheValues[$attributeName];
		}
		else
		{
			return null;
		}
	}

	public function setAttributeValue($attributeName, $attributeValue)
	{
		// TODO: implement validation

		$attributeData = array (
		'stepId' => $this->getContext()->getStepId(), 'name' => $attributeName, 'value' => $attributeValue);
		$this->getAttributeModel()->replace($attributeData);

		if (!is_null($this->attributeCacheValues))
		{
			$this->getAttributeValue($attributeName, true);
		}
	}

	public function unsetAttribute($attributeName)
	{
		// TODO: implement validation

		$db = $this->getDb();
		$db->query('DELETE FROM sophie_treatment_step_eav WHERE stepId = ' . $this->getContext()->getStepId() . ' AND name = ' . $db->quote($attributeName));

		if (!is_null($this->attributeCacheValues))
		{
			unset($this->attributeCacheValues[$attributeName]);
		}
	}

	// runtime attributes
	public function initAttributeRuntimeValues($forceReloadPersistent = false)
	{
		$this->attributeRuntimeValues = $this->getContext()->getStepAttributes();
	}

	public function getAttributeRuntimeValues($forceReset = false, $forceReloadPersistent = false)
	{
		if (is_null($this->attributeRuntimeValues) || $forceReset)
		{
			$this->initAttributeRuntimeValues($forceReloadPersistent);
		}
		return $this->attributeRuntimeValues;
	}

	public function getAttributeRuntimeValue($attributeName, $forceReset = false, $forceReloadPersistent = false)
	{
		if (is_null($this->attributeRuntimeValues) || $forceReset)
		{
			$this->initAttributeRuntimeValues($forceReloadPersistent);
		}

		if (array_key_exists($attributeName, $this->attributeRuntimeValues))
		{
			return $this->attributeRuntimeValues[$attributeName];
		}
		else
		{
			return null;
		}
	}

	public function setRuntimeAttributeValue($attributeName, $attributeValue)
	{
		if (is_null($this->attributeRuntimeValues))
		{
			// TODO: sure this should not happen, but why should we fail here? Instead init:
			$this->initAttributeRuntimeValues();
			// trigger_error('Could not set attribute: Attributes not initialized yet.', E_USER_WARNING);
			// return false;
		}

		$attributeConfiguration = $this->getAttributeConfiguration($attributeName);
		if (is_null($attributeConfiguration))
		{
			// TODO: throw an Exception here?
			trigger_error('Could not set attribute: Attribute ' . $attributeName . ' unknown.', E_USER_WARNING);
			return false;
		}

		if (isset($attributeConfiguration['setByApi']) && $attributeConfiguration['setByApi'] == false)
		{
			// TODO: throw an Exception here?
			trigger_error('Could not set attribute: Attribute ' . $attributeName . ' may not be set at runtime.', E_USER_WARNING);
			return false;
		}

		if ($this->validateAttributeValueAgainstConfiguration($attributeName, $attributeValue))
		{
			$this->attributeRuntimeValues[$attributeName] = $attributeValue;
		}
		return true;
	}

	// TODO: do we need an unsetRuntimeAttribute?
	public function unsetRuntimeAttribute($attributeName)
	{
		trigger_error('Could not unset attribute: function is not implemented', E_USER_WARNING);
	}

	/*
	 * API AND RENDERER INIT
	 */

	public function getPhpSanitizer($reset = true)
	{
		if (is_null($this->phpSanitizer) || $reset)
		{
			$this->phpSanitizer = new Sophie_Validate_PHPCode();
		}
		return $this->phpSanitizer;
	}

	public function getLocale()
	{
		if (is_null($this->locale))
		{
			$treatment = $this->getContext()->getTreatment();
			if ($treatment['defaultLocale'] == '')
			{
				$treatment['defaultLocale'] = 'en_US';
			}
			$this->locale = new Zend_Locale($treatment['defaultLocale']);
		}
		return $this->locale;
	}

	public function addTranslations()
	{
		$translationPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'translate';
		if (!in_array($translationPath, $this->translatePaths))
		{
			$this->translatePaths[] = $translationPath;
		}

		foreach ($this->translatePaths as $translationPath)
		{
			if (is_dir($translationPath))
			{
				foreach (scandir($translationPath) as $translateFile)
				{
					if (substr($translateFile, 0, 1) == '.' || strlen($translateFile) < 4 || substr($translateFile, strlen($translateFile) - 4) != '.php')
					{
						continue;
					}

					$translateFileLocale = substr($translateFile, 0, strlen($translateFile) - 4);

					$this->translate->addTranslation(array (
						'content' => $translationPath . DIRECTORY_SEPARATOR . $translateFile,
						'locale' => new Zend_Locale($translateFileLocale
					)));
				}
			}
		}
	}

	public function getTranslate()
	{
		if (is_null($this->translate))
		{
			$this->translate = new Zend_Translate(array (
				'adapter' => 'array',
				'content' => array (),
				'locale' => new Zend_Locale('en_US'
			), 'disableNotices' => true));

			$this->addTranslations();
		}

		$this->translate->setLocale($this->getLocale());
		return $this->translate;
	}

	/*
	 *
	 * RENDER AND PROCESS FRONTEND
	 *
	 */

	private function getTreatmentLayoutTheme()
	{
		$treatment = $this->getContext()->getTreatment();
		if ($treatment['layoutTheme'] == '')
		{
			$config = Zend_Registry::get('config');
			return $config['systemConfig']['sophie']['expfront']['defaultLayoutTheme'];
		}
		else
		{
			return $treatment['layoutTheme'];
		}
	}

	private function getTreatmentLayoutDesign()
	{
		$treatment = $this->getContext()->getTreatment();
		if ($treatment['layoutDesign'] == '')
		{
			$config = Zend_Registry::get('config');
			return $config['systemConfig']['sophie']['expfront']['defaultLayoutDesign'];
		}
		else
		{
			return $treatment['layoutDesign'];
		}
	}

	private function getTreatmentCSS()
	{
		$treatment = $this->getContext()->getTreatment();
		return $treatment['css'];
	}

	final public function init()
	{
		// call for all attributes to initialize them.
		$initializationScript = $this->getAttributeRuntimeValue('initializationScript');
		// now the attributes may be overridden by $stepApi->setAttribute(...)
		if (!empty($initializationScript))
		{
			$scriptSandbox = $this->getScriptSandbox();
			$scriptSandbox->run($initializationScript);

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
				Sophie_Db_Session_Log :: log($this->getContext()->getSessionId(), 'initializationScript: ' . $evalOutputShort, 'debug', $evalOutput);
			}
			$scriptSandbox->clearEvalOutput();
		}
	}

	public function run()
	{
		$context = $this->getContext();
		$controller = $this->getController();
		$db = $context->getDb();
		$sessionId = $context->getSessionId();
		$session = $context->getSession();
		$stepId = $context->getStepId();

		///////////////////////////////////////////////////////
		// check process step contextId
		///////////////////////////////////////////////////////
		$contextChecksum = $controller->getRequest()->getParam('contextChecksum', null);
		if (!is_null($contextChecksum) && $contextChecksum == $context->getChecksum())
		{

			///////////////////////////////////////////////////////
			// process step
			///////////////////////////////////////////////////////
			try
			{
				$context->beginTransaction();

				// return false means: step is no more uptodate -> goto current step in next loop
				if ($context->isUpToDate() !== true)
				{
					$db->rollBack();
					Sophie_Db_Session_Log :: log($sessionId, 'Context outdated when process', 'warning');
					return false;
				}

				// return false means: do not process -> stay in this step and render
				// return true means: process step
				if ($this->preProcess() !== true)
				{
					$db->commit();
					Sophie_Db_Session_Log :: log($sessionId, 'Preprocess returned false', 'debug');
				}

				else
				{
					// init error handler
					Sophie_Eval_Error_Handler :: $context = $context;
					Sophie_Eval_Error_Handler :: $script = 'Process Step';
					set_error_handler(array('Sophie_Eval_Error_Handler', 'errorHandler'));

					// return false means: processing failed -> stay in this step and render
					// return true means: processing successfull -> do not render and goto next step
					$processResult = $this->process();

					// restore error handler
					restore_error_handler();

					$this->postProcess($processResult);

					if ($processResult !== true)
					{
						Sophie_Db_Session_Log::log($sessionId, 'Process returned false', 'debug');
						$db->commit();
					}
					else
					{
						$controller->getRequest()->setParam('contextChecksum', null);
						$db->commit();
						return false;
					}
				}
			}
			catch (Exception $e)
			{
				// TODO: differentiate scenarios: application error, transaction timeout, db connection, deadlock
				// rollback and try again

				$db->rollBack();
				Sophie_Db_Session_Log :: log($sessionId, 'Had to rollback!', 'error', print_r($e, true));

				// take a deep breath here and try again
				usleep(250);

				// TODO: handle this in a better way!
				// implement an error page, reset request and add error to rendered page or a special loop counter to prevent infinite processing (general front controller loop limit might already be enough)
				//die('Had to rollback: ' . print_r($e, true));
				return false;
			}
		}

		///////////////////////////////////////////////////////////
		// render step
		///////////////////////////////////////////////////////////

		$this->preRender();

		// init error handler
		Sophie_Eval_Error_Handler :: $context = $context;
		Sophie_Eval_Error_Handler :: $script = 'Render';
		set_error_handler(array('Sophie_Eval_Error_Handler', 'errorHandler'));

		$renderResult = $this->renderComposite();

		// restore error handler
		restore_error_handler();

		$controller->getResponse()->appendBody($renderResult);
		$this->postRender();

		if ($session['debugConsole'] == 1)
		{
			$debugConsole = array();

			$stepgroup = $context->getStepgroup();
			$step = $context->getStep();
			$participant = $context->getParticipant();

			$debugConsole[] = 'Stepgroup / Loop: ' . $stepgroup['name'] . ' (' . $participant['stepgroupLoop'] . ')';
			$debugConsole[] = 'Step: ' . $step['name'];
			$debugConsole[] = 'Participant Label: ' . $participant['label'];
			if ($stepgroup['grouping'] == 'inactive' && $session['participantMgmt'] == 'static')
			{
					$debugConsole[] = 'Group: inactive';
			}
			else
			{
				$groupLabel = $context->getApi('group')->translateLabel('%current%');
				if (is_null($groupLabel))
				{
					$debugConsole[] = 'Group: none';
				}
				else
				{
					$groupMembers = $context->getApi('group')->getGroupMemberLabels($groupLabel);
					$debugConsole[] = 'Group - Label: ' . $groupLabel .  ' / Members: ' . implode(', ', $groupMembers);
				}
			}

			$debugConsole[] = '';
			if (defined('START_TIME'))
			{
				$debugConsole[] = 'Processing Time: ' . ceil((microtime(true) - START_TIME) * 1000) . 'ms';
			}
			$debugConsole[] = 'Memory Usage - Current: ' . round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB / Peak: ' . round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB';
			$controller->view->placeholder('debugConsole')->set(implode("\n", $debugConsole));
		}
		return true;
	}

	public function preRender()
	{
		$view = $this->getView();
		$view->participantLabel = $this->getContext()->getParticipantLabel();

		$additionalTreatmentCSS = $this->getTreatmentCSS();
		$additionalStepCSS = $this->getAttributeRuntimeValue('layoutAdditionalCSS');

		if ($additionalTreatmentCSS != '')
		{
			$view->headStyle()->appendStyle($additionalTreatmentCSS, array('media'=>'all'));
		}

		if ($additionalStepCSS != '')
		{
			$view->headStyle()->appendStyle($additionalStepCSS, array('media'=>'all'));
		}

		$stepLayoutTheme = $this->getAttributeRuntimeValue('layoutTheme');
		if ($stepLayoutTheme != '')
		{
			$layoutTheme = $stepLayoutTheme;
		}
		else
		{
			$layoutTheme = $this->getTreatmentLayoutTheme();
		}

		$layout = $this->getController()->getHelper('layout')->getLayoutInstance();
		$layoutPath = BASE_PATH . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'sophie' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $layoutTheme . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'scripts';
		if (!is_dir($layoutPath))
		{
			throw new Exception('Selected theme does not exist: ' . $layoutTheme);
		}
		$layout->setLayoutPath($layoutPath);

		$stepLayoutDesign = $this->getAttributeRuntimeValue('layoutDesign');
		if ($stepLayoutDesign != '')
		{
			$layoutDesign = $stepLayoutDesign;
		}
		else
		{
			$treatmentLayoutDesign = $this->getTreatmentLayoutDesign();
			if ($treatmentLayoutDesign == '')
			{
				$layoutName = 'default';
			}
			else
			{
				$layoutDesign = $treatmentLayoutDesign;
			}
		}
		$layout->setLayout($layoutDesign);
	}

	public function renderComposite()
	{
		$view = $this->getView();
		$stepRender = $this->getStepRenderer();

		$timerApi = $this->getContext()->getApi('timer');
		$timerEnabled = $timerApi->isEnabled();

		$stepgroupLoop = $this->getContext()->getStepgroupLoop();
		$stepId = $this->getContext()->getStepId();
		$participantLabel = $this->getContext()->getParticipantLabel();

		$previewMode = $this->getContext()->getPreviewMode();

		if (($timerEnabled === true || $this->options[self :: STEPSYNC][self :: ENABLED]) && $previewMode === false)
		{
			$frontendParams = array(
				'_timerEnabled' => $timerEnabled,
				'_timerWarning' => $view->escape($this->getAttributeRuntimeValue('timerOnTimeoutWarning')),
				'_timerShowOnStartup' => $view->escape($this->getAttributeRuntimeValue('timerShowOnStartup')),
				'_timerShowOnCountdown' => $view->escape($this->getAttributeRuntimeValue('timerShowOnCountdown')),
				'_timerGracePeriodClient' => $timerApi->getGracePeriodClient(),
				'_timerStartTime' => $timerApi->getStartTime(),
				'_timerDuration' => $timerApi->getDuration(),
				'_timerCountdownEnabled' => $timerApi->isCountdownEnabled(),
				'_timerCountdownDuration' => $timerApi->getCountdownDuration(),
				'_timerProceedBeforeTimeout' => $timerApi->isTimerProceedBeforeTimeout(),
				'_timerOnTimeout' => $timerApi->getTimerOnTimeout(),
				'_timerDisplay' => $timerApi->isTimerDisplay(),
			);

			$syncLoopLimit = 0;
			$config = Zend_Registry::get('config');
			if (isset($config['systemConfig']['sophie']['expfront']['ajaxStepsyncLoopLimit']))
			{
				$syncLoopLimit = (int)$config['systemConfig']['sophie']['expfront']['ajaxStepsyncLoopLimit'];
			}
			if (empty($syncLoopLimit) || $syncLoopLimit <= 1)
			{
				$frontendParams['stepSyncInterval'] = 1800;
			}
			else
			{
				$frontendParams['stepSyncInterval'] = 250;
			}

			$frontendParams = Zend_Json::encode($frontendParams);
			$view->inlineScript()->appendFile('/_scripts/sophie/Frontend.js');
			$view->jsOnLoad()->appendScript('window.sophieFrontend.init("' . $view->escape($this->getContext()->getChecksum()) . '", ' . $frontendParams . ');');
		}

		$composite = '';

		$composite .= '<div id="sophie_steploading" class="hidden sophie_loading"><img src="/_media/ajax-loader.gif" /></div>';

		$composite .= '<div id="sophie_stepcontent"';
		if ($timerEnabled && $previewMode === false)
		{
			$composite .= ' class="hidden"';
		}
		$composite .= '>';
		$composite .= $this->render();
		$composite .= '</div>';

		// assemble step startup content
		$timerStartupHeadline = $this->getAttributeRuntimeValue('timerStartupHeadline');
		$timerStartupBody = $this->getAttributeRuntimeValue('timerStartupBody');

		if ($timerStartupHeadline != '' || $timerStartupBody != '')
		{
			$composite .= '<div id="sophie_stepstartup" class="hidden">';
			$timerStartupHeadline = $stepRender->render($timerStartupHeadline);
			$timerStartupBody = $stepRender->render($timerStartupBody);

			// assemble content
			$composite .= '<div class="cheader">';
			if ($timerStartupHeadline != '')
			{
				$composite .= '<div class="cheadline">' . $timerStartupHeadline . '</div>';
			}
			if ($timerStartupBody != '')
			{
				$composite .= '<div class="cheadtext">';
				$composite .= $timerStartupBody;
				$composite .= '</div>';
			}
			$composite .= '</div>';

			$composite .= '</div>';
		}
		else
		{
			$composite .= '<div id="sophie_stepstartup" class="hidden sophie_loading">';
			$composite .= '<img src="/_media/ajax-loader.gif" />';
			$composite .= '</div>';
		}

		// assemble step countdown content
		$timerCountdownHeadline = $this->getAttributeRuntimeValue('timerCountdownHeadline');
		$timerCountdownBody = $this->getAttributeRuntimeValue('timerCountdownBody');


		$countdownComposite = '';
		if ($timerCountdownHeadline != '' || $timerCountdownBody != '')
		{
			$useDefaultTimer = false;
			$timerCountdownHeadline = $stepRender->render($timerCountdownHeadline);

			// assemble content
			$countdownComposite .= '<div class="cheader">';
			if ($timerCountdownHeadline != '')
			{
				$countdownComposite .= '<div class="cheadline">' . $timerCountdownHeadline . '</div>';
			}

			if ($timerCountdownBody != '')
			{
				$timerCountdownBody = $stepRender->render($timerCountdownBody);
				$timerCountdownBody = str_replace('{#countdown#}', '<div id="sophie_countdown_timer"></div>', $timerCountdownBody);
			}
			else
			{
				$timerCountdownBody = '<div id="sophie_countdown_timer" class="sophie_countdown"></div>';
			}

			if ($timerCountdownBody != '')
			{
				$countdownComposite .= '<div class="cheadtext">';
				$countdownComposite .= $timerCountdownBody;
				$countdownComposite .= '</div>';
			}

			$countdownComposite .= '</div>';
		}
		else
		{
			$useDefaultTimer = true;
			$countdownComposite .= '<div id="sophie_countdown_timer"></div>';
		}

		$composite .= '<div id="sophie_stepcountdown" class="hidden';
		if ($useDefaultTimer)
		{
			$composite .= ' sophie_stepcountdown';
		}
		$composite .= '">' . $countdownComposite . '</div>';

		$composite .= '<div id="sophie_stepend" class="hidden sophie_loading"><img src="/_media/ajax-loader.gif" /></div>';

		$composite .= '<form class="hidden" id="sophie_form_nextstep" action="' . $this->getFrontUrl() . '" method="POST">';
		$composite .= $view->formHidden('contextChecksum', $this->getContext()->getChecksum());
		$composite .= '</form>';

		return $composite;
	}

	// overwrite this one!
	public function render()
	{
		/* Proof of concept for usage of Zend View for frontend rendering
		$viewBasePath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'views';
		$viewScript =  $viewBasePath . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'frontend.phtml';
		if (file_exists($viewScript))
		{
		  $view = $this->getView();
		  $view->addBasePath($viewBasePath);
		  $view->steptype = $this;
		  return $view->render('frontend.phtml');
		}
		return 'No View available';
		*/
	}

	public function postRender()
	{
	}

	public function preProcess()
	{
		$timerApi = $this->getContext()->getApi('timer');
		
		if (!$timerApi->isEnabled())
		{
			return true;
		}

		$state = $timerApi->getGracefulState();
		
		if ($state == 'ended')
		{
			if ($timerApi->getTimerOnTimeout() == 'continue')
			{
				// do not process after timeout when onTimeout is set to continue
				// TODO: decide if tranferToNextStep should be called here
				return false;
			}
			else
			{
				return true;
			}
		}

		if (($state == 'notstarted' || $state == 'started') && $timerApi->getTimerShowOnStartup() != 'mainContent')
		{
			return false;
		}
		
		elseif ($state == 'countdown' && $timerApi->getTimerShowOnCountdown() != 'mainContent')
		{
			return false;
		}

		// mainContent is shown
		if ($timerApi->isTimerProceedBeforeTimeout())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function process()
	{
		$this->getContext()->getApi('process')->transferParticipantToNextStep();
		Sophie_Db_Session_Log::log($this->getContext()->getSessionId(), 'Moved participant ' . $this->getContext()->getParticipantLabel() . ' to next step after ' . $this->getContext()->getStepLabel() . ' after processing', 'debug');
		return true;
	}

	public function postProcess($processSuccess)
	{
		if ($processSuccess)
		{
			$timerApi = $this->getContext()->getApi('timer');
			if ($timerApi->isEnabled() && $timerApi->getTimerProceedBeforeTimeout())
			{
				$timerProceedTimeVarname = $this->getAttributeRuntimeValue('timerProceedTimeVarname');

				if (!empty($timerProceedTimeVarname))
				{
					$this->getContext()->getApi('variable')->setPSL($timerProceedTimeVarname, $timerApi->getStartTime());
				}

				$timerRemainingTimeVarname = $this->getAttributeRuntimeValue('timerRemainingTimeVarname');
				if (!empty($timerRemainingTimeVarname))
				{
					$this->getContext()->getApi('variable')->setPSL($timerRemainingTimeVarname, $timerApi->getRemainingTime());
				}
			}
		}
	}

	public function preAjaxProcess()
	{
		$timerApi = $this->getContext()->getApi('timer');

		if ($timerApi->isEnabled())
		{
			$state = $timerApi->getGracefulState();

			if ($state === 'ended' && $timerApi->getTimerOnTimeout()=== 'continue')
			{
				return false;
			}

			if ($state !== 'running')
			{
				return false;
			}
		}
		return true;
	}

	public function ajaxProcess()
	{
	}

	public function ajaxSync()
	{
		$timerApi = $this->getContext()->getApi('timer');
		// TODO: this function needs to be refactored using timerApi
		$result = array();

		// return if timer is disabled
		$result['timerEnabled'] = $timerApi->isEnabled();

		if (!$result['timerEnabled'])
		{
			//$result['state'] = 'content';
			return $result;
		}

		//$result['timerContext'] = $timerApi->getTimerContext();
		$result['timerDisplay'] = $timerApi->isTimerDisplay();
		$result['timerCountdownEnabled'] = $timerApi->isCountdownEnabled();
		$result['timerCountdownDuration'] = $timerApi->getCountdownDuration();
		$result['timerProceedBeforeTimeout'] = $timerApi->isTimerProceedBeforeTimeout();
		$result['timerOnTimeout'] = $timerApi->getTimerOnTimeout();
		$result['timerStartTime'] = $timerApi->getStartTime();
		$result['timerDuration'] = $timerApi->getDuration();
		//$result['timerGracePeriodServer'] = $timerApi->getGracePeriodServer();
		$result['timerGracePeriodClient'] = $timerApi->getGracePeriodClient();
		//$result['time'] = ceil(microtime(true) * 1000);

/*		the state is derived in javascript depending on the parameters above
		$state = $timerApi->getGracefulState();

		if ($state === 'notstarted')
		{
			$result['timerState'] = self :: TIMER_NOT_RUNNING;
			$result['state'] = 'startup';
			return $result;
		}

		// time is before timer start
		if ($state === 'countdown')
		{
			$result['timerState'] = self :: TIMER_COUNTDOWN_RUNNING;
			if ($result['timerCountdownEnabled'])
			{
				$result['state'] = 'countdown';
			}
			else
			{
				$result['state'] = 'startup';
			}
			return $result;
		}

		// time has run out
		if ($state === 'ended')
		{
			$result['timerState'] = self :: TIMER_EXPIRED;
			if ($result['timerOnTimeout'] == 'continue')
			{
				$result['state'] = 'nextStep';
			}
			else
			{
				$result['state'] = 'content';
			}
			return $result;
		}

		// timer is active
		$result['timerState'] = self :: TIMER_RUNNING;
		$result['state'] = 'content';*/

		return $result;
	}

	/*
	 * evalutae run conditions
	 */

	public function evaluateExpression($type, $param)
	{
		$variableApi = $this->getContext()->getApi('variable');

		$value = null;
		switch ($type)
		{
			case 'value' :
				$value = $param;
				break;

			case 'stepgroupLoop' :
				$value = $this->getContext()->getStepgroupLoop();
				break;

			case 'participantVar' :

				$value = $variableApi->getPSL($param);
				break;

			case 'groupVar' :
				$value = $variableApi->getGSL($param);
				break;

			case 'globalVar' :
				$value = $variableApi->getESL($param);
				break;

		}
		return $value;
	}

	public function checkRunCondition()
	{
		$run = true;

		/*		for ($i = 0; $i <= 10; $i++)
		    {
		      $type1 = $this->getAttributeRuntimeValue('runCondition' . $i . 'Param1Type');

		      if (empty($type1))
		      {
		        break;
		      }

		      $expression1 = null;
		      $expression2 = null;

		      $type1 = $this->getAttributeRuntimeValue('runCondition' . $i . 'Param1Type');
		      $param1 = $this->getAttributeRuntimeValue('runCondition' . $i . 'Param1');
		      $expression1 = $this->evaluateExpression($type1, $param1);

		      $operator = $this->getAttributeRuntimeValue('runCondition' . $i . 'Operator');

		      if ($operator == 'empty')
		      {
		        if (!empty ($expression1))
		        {
		          $run = false;
		        }
		      }
		      elseif ($operator == '!empty')
		      {
		        if (empty ($expression1))
		        {
		          $run = false;
		        }
		      }
		      else
		      {
		        $type2 = $this->getAttributeRuntimeValue('runCondition' . $i . 'Param2Type');
		        $param2 = $this->getAttributeRuntimeValue('runCondition' . $i . 'Param2');
		        $expression2 = $this->evaluateExpression($type2, $param2);

		        switch ($operator)
		        {
		          case '>' :
		          case '<' :
		          case '==' :
		          case '<>' :
		          case '>=' :
		          case '<=' :
		            eval ('$run = ($expression1 ' . $operator . ' $expression2);');
		            break;
		        }
		      }

		      if ($run === false)
		        break;
		    }
		*/

		$runConditionScript = $this->getAttributeRuntimeValue('runConditionScript');
		if (!empty ($runConditionScript))
		{
			$scriptSandbox = $this->getScriptSandbox();
			$scriptReturn = $scriptSandbox->run($runConditionScript);
			if (is_null($scriptReturn))
			{
				$runConditionDefault = $this->getAttributeRuntimeValue('runConditionDefault');
				$run = ($runConditionDefault == 1);
			}
			else
			{
				$run = (bool) $scriptReturn;
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
				Sophie_Db_Session_Log :: log($this->getContext()->getSessionId(), 'runConditionScript: ' . $evalOutputShort, 'debug', $evalOutput);
			}
			$scriptSandbox->clearEvalOutput();
		}

		return $run;

	}

	////////////////////////////////////////////
	// admin process
	////////////////////////////////////////////

	// LET THE ADMIN PROCESS SOMETHING
	public function runAdminProcess()
	{

	}

	protected function runAdminProcessTimerEverywhereContext($context)
	{
		$timerApi = $context->getApi('timer');
		$timerState = $timerApi->getGracefulState();

		// check if participants should be moved to the next step
		if ($timerState === 'ended' && $timerApi->getTimerOnTimeout() === 'continue')
		{
			$context->getApi('process')->transferEveryoneToNextStep();
			Sophie_Db_Session_Log::log($context->getSessionId(), 'Moved everyone to next step from ' . $context->getStepLabel() . ' due to expired timer', 'notice');
			return;
		}

		// autostart timer
		if ($timerState === 'notstarted')
		{
			$timerStart = $timerApi->getTimerStart();
			$syncApi = $context->getApi('sync');
			if ($timerStart === 'first-in-context' || ($timerStart === 'sync-context' && $syncApi->checkEveryone()))
			{
				// start timer automatically
				$timerApi->start();
				Sophie_Db_Session_Log::log($context->getSessionId(), 'Autostarted timer for step ' . $context->getStepId(), 'notice');
			}
		}
	}

	protected function runAdminProcessTimerGroupContext($context)
	{
		$timerApi = $context->getApi('timer');
		if (!$timerApi->isEnabled())
		{
			return;
		}

		$timerState = $timerApi->getGracefulState();

		// check if participants should be moved to the next step
		if ($timerState === 'ended' && $timerApi->getTimerOnTimeout() === 'continue')
		{
			$context->getApi('process')->transferGroupToNextStep();
			Sophie_Db_Session_Log::log($context->getSessionId(), 'Moved group ' . $context->getGroupLabel() . ' to next step from ' . $context->getStepLabel() . ' due to expired timer', 'notice');
			return;
		}

		// autostart timer
		if ($timerState === 'notstarted')
		{
			$timerStart = $timerApi->getTimerStart();
			$syncApi = $context->getApi('sync');
			if ($timerStart === 'first-in-context' || ($timerStart === 'sync-context' && $syncApi->checkGroup()))
			{
				// start timer automatically
				$timerApi->start();
				Sophie_Db_Session_Log :: log($context->getSessionId(), 'Autostarted timer for group ' . $context->getGroupLabel() . ' step ' . $context->getStepId(), 'notice');
			}
		}
	}

	protected function runAdminProcessTimerParticipantContext($context)
	{
		$timerApi = $context->getApi('timer');
		if (!$timerApi->isEnabled())
		{
			return;
		}

		$timerState = $timerApi->getGracefulState();

		// check if participants should be moved to the next step
		if ($timerState === 'ended' && $timerApi->getTimerOnTimeout() === 'continue')
		{
			$context->getApi('process')->transferParticipantToNextStep();
			Sophie_Db_Session_Log::log($context->getSessionId(), 'Moved participant ' . $context->getParticipantLabel() . ' to next step from ' . $context->getStepLabel() . ' due to expired timer', 'notice');
			return;
		}

		// autostart timer
		if ($timerState === 'notstarted')
		{
			$timerStart = $timerApi->getTimerStart();
			if ($timerStart === 'sync-context' || $timerStart === 'first-in-context')
			{
				$timerApi->start();
				Sophie_Db_Session_Log::log($context->getSessionId(), 'Autostarted timer for participant ' . $context->getParticipantLabel() . ' step ' . $context->getStepId(), 'notice');
			}
		}
	}

	public function runAdminProcessTimer()
	{
		$context = $this->getContext();
		$timerApi = $context->getApi('timer');

		// if timer is not activated leave the function
		if (!$timerApi->isEnabled())
		{
			return;
		}

		$timerContext = $timerApi->getTimerContext();

		/////////////////////////////////////////
		// check timer context for everyone
		/////////////////////////////////////////
		if ($timerContext === 'E')
		{
			$this->runAdminProcessTimerEverywhereContext($context);
		}

		/////////////////////////////////////////
		// check timer context for group
		/////////////////////////////////////////
		elseif ($timerContext === 'G')
		{
			$db = Zend_Registry::get('db');
			$select = $db->select();
			$select->from(array (
			'p' => Sophie_Db_Session_Participant :: getInstance()->_name), array (
				'num' => new Zend_Db_Expr('count(*)'
			)));
			$select->joinLeft(array (
			'g' => Sophie_Db_Session_Participant_Group :: getInstance()->_name), 'p.sessionId = g.sessionId AND p.label = g.participantLabel AND p.stepgroupLabel = g.stepgroupLabel AND p.stepgroupLoop = g.stepgroupLoop', array (
				'groupLabel'
			));
			$select->where('p.sessionId = ?', $context->getSessionId());
			$select->where('p.stepId = ' . $db->quote($context->getStepId()) . ' AND p.stepgroupLabel = ' . $db->quote($context->getStepgroupLabel()) . ' AND p.stepgroupLoop = ' . $db->quote($context->getStepgroupLoop()) . ' AND NOT p.stepId IS NULL AND p.state <> "finished"');
			$select->group(array (
				'p.sessionId',
				'p.stepgroupLabel',
				'p.stepgroupLoop',
				'p.stepId',
				'g.groupLabel'
			));

			$groups = $select->query()->fetchAll();

			foreach ($groups as $group)
			{
				if (empty($group['groupLabel']))
				{
					continue;
				}

				$groupContext = clone $context;
				$groupContext->setPersonContextLevel('group');
				$groupContext->setGroupLabel($group['groupLabel']);

				$groupTimerApi = $groupContext->getApi('timer');

				$this->runAdminProcessTimerGroupContext($groupContext);
			}
		}

		/////////////////////////////////////////
		// check timer context for participant
		/////////////////////////////////////////
		elseif ($timerContext === 'P')
		{
			$db = Zend_Registry::get('db');
			$select = $db->select();
			$select->from(array(
			'p' => Sophie_Db_Session_Participant :: getInstance()->_name));
			$select->where('p.sessionId = ?', $context->getSessionId());
			$select->where('p.stepId = ' . $db->quote($context->getStepId()) . ' AND p.stepgroupLabel = ' . $db->quote($context->getStepgroupLabel()) . ' AND p.stepgroupLoop = ' . $db->quote($context->getStepgroupLoop()) . ' AND NOT p.stepId IS NULL AND p.state <> "finished"');

			$participants = $select->query()->fetchAll();

			foreach ($participants as $participant)
			{
				$participantContext = clone $context;
				$participantContext->setPersonContextLevel('participant');
				$participantContext->setParticipantLabel($participant['label']);

				$this->runAdminProcessTimerParticipantContext($participantContext);
			}
		}
	}

	//////////////////////////////////////////////////////
	// INIT AND RENDER ADMIN FORM
	//////////////////////////////////////////////////////

	public function adminGetSteptypeSystemName()
	{
		return basename($this->getBasePath());
	}

	public function adminGetSteptypeInfoName()
	{
		$steptypeInfo = $this->adminGetSteptypeInfo();
		return $steptypeInfo['name'];
	}

	public function adminGetSteptypeInfo()
	{
		if (is_null($this->adminSteptypeInfo))
		{
			$steptypeModel = Sophie_Db_Steptype :: getInstance();
			$this->adminSteptypeInfo = $steptypeModel->find($this->adminGetSteptypeSystemName())->current()->toArray();
		}
		return $this->adminSteptypeInfo;
	}

	// on create use this function to preset values
	public function adminSetDefaultValues()
	{

	}

	public function adminRender()
	{
		$form = $this->adminGetForm();

		$step = $this->getContext()->getStep();
		$form->setLegend('Edit Step ' . $this->adminGetSteptypeInfoName() . ' - ' . $step['name']);

		return $form->render($this->getView());
	}

	public function adminGetForm()
	{
		if (is_null($this->_adminForm))
		{
			$this->_adminForm = new Sophie_Steptype_Admin_Form();
			$this->_adminForm->setName('adminForm');
			$view = $this->getView();
			$this->_adminForm->setAction($view->url());

			$this->adminInitSubForms();

			$step = $this->getContext()->getStep();
			$this->_adminForm->addElement('hidden', 'stepId', array (
				'value' => $step['id']
			));
			//			$this->_adminForm->addElement('hidden', 'adminSubmitAction', array('value'=>'save'));
			//			$this->_adminForm->addElement('submit', 'save', array('title'=>'Save'));
			//			$this->_adminForm->addElement('submit', 'saveAndReturn', array('title'=>'Save and Return'));
		}
		return $this->_adminForm;
	}

	public function adminInitSubForms()
	{
		$tabs = $this->adminGetOrderedTabs();
		foreach ($tabs as $tab)
		{
			$tabFunction = 'admin' . ucfirst($tab['id']) . 'TabInit';
			if (method_exists($this, $tabFunction))
			{
				$this-> $tabFunction ();
			}
		}
	}

	public function adminGetTabs()
	{
		$tabs = array ();
		if ($this->options[ self :: RUN_CONDITIONS ][ self :: ENABLED ])
		{
			$tabs[] = array (
				'id' => 'runConditions',
				'title' => 'Run Conditions',
				'order' => 500
			);
		}
		if ($this->options[ self :: TIMER ][ self :: ENABLED ])
		{
			$tabs[] = array (
				'id' => 'timer',
				'title' => 'Timer',
				'order' => 600
			);
		}
		$tabs[] = array (
				'id' => 'runtimeinitialization',
				'title' => 'Runtime Initialization',
				'order' => 700
		);
		$tabs[] = array (
				'id' => 'layout',
				'title' => 'Layout',
				'order' => 800
		);
		$tabs[] = array (
				'id' => 'internal',
				'title' => 'Internal',
				'order' => 900
		);
		if ($this->adminHelpTabHasContent())
		{
			$tabs[] = array (
				'id' => 'help',
				'title' => 'Help',
				'order' => 1000
			);
		}
		return $tabs;
	}

	public function adminGetOrderedTabs()
	{
		$tabs = $this->adminGetTabs();
		$orderedTab = array ();
		foreach ($tabs as $tab)
		{
			$orderedTab[$tab['order']] = $tab;
		}
		ksort($orderedTab);
		return $orderedTab;
	}

	public function adminTimerTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subformName = 'timer';
		$subForm = $form->getSubForm($subformName);
		if (is_null($subForm))
		{
			$subForm = $form->createSubForm();
			$subForm->setAttribs(array (
				'legend' => 'Timer',
				'dijitParams' => array (
					'title' => 'Timer',

				)
			));
			$form->addSubForm($subForm, $subformName);
		}

		$order = 0;

		if ($this->options[ self :: TIMER ][ self :: FORCED ])
		{
			$order += 100;
			$isTimerEnabled = true;
			$timerEnabled = $subForm->createElement('StaticHtml', 'timerEnabled',
				array (
					'label' => 'Enable Timer',
					'value' => 'Timer is always enabled for this steptype',
					'order' => $order
				));
		}
		else
		{
			$order += 100;
			$isTimerEnabled = $this->getAttributeValue('timerEnabled');
			$timerEnabled = $subForm->createElement('Checkbox', 'timerEnabled',
				array (
					'label' => 'Enable Timer',
					'value' => '1',
					'checked' => false,
					'order' => $order
				));
			$timerEnabled->setChecked($isTimerEnabled);
		}

		$order += 100;
		$timerDisplay = $subForm->createElement('Checkbox', 'timerDisplay', array (
			'label' => 'Show Timer',
			'value' => '1',
			'checked' => false,
			'order' => $order
		), array ());
		$timerDisplay->setChecked($this->getAttributeValue('timerDisplay'));

		$order += 100;
		$timerContextOptions = array (
			'E' => 'For Everyone',
			'G' => 'Per Group',
			'P' => 'Per Participant'
		);
		$timerContext = $subForm->createElement('select', 'timerContext', array (
			'multiOptions' => $timerContextOptions,
			'label' => 'Timer Context',
			'required' => true,
			'order' => $order
		));
		$timerContext->setValue($this->getAttributeValue('timerContext'));

		$order += 100;
		$timerStartOptions = array (
			'admin' => 'Manually started by the Experimenter',
			'sync-context' => 'Automatically, once all participants from the selected context are present',
			'first-in-context' => 'Automatically, once the first participant from the selected context is present'
		);
		$timerStart = $subForm->createElement('Select', 'timerStart', array (
			'autoInsertNotEmptyValidator' => false,
			'multiOptions' => $timerStartOptions,
			'label' => 'Timer Start',
			'required' => true,
			'order' => $order
		));
		$timerStart->setValue($this->getAttributeValue('timerStart'));

		$order += 100;
		$timerDuration = $subForm->createElement('TimerInput', 'timerDuration', array (
			'label' => 'Timer Duration',
			'required' => $isTimerEnabled,
			'step' => 1,
			'min' => '00:00:00',
			'order' => $order,
		));
		$timerDuration->setValueAsNumber($this->getAttributeValue('timerDuration'));

		$order += 100;
		$isTimerCountdownEnabled = $this->getAttributeValue('timerCountdownEnabled');
		$timerCountdownEnabled = $subForm->createElement('Checkbox', 'timerCountdownEnabled',
			array (
				'label' => 'Enable Countdown',
				'value' => '1',
				'checked' => false,
				'order' => $order,
			));
		$timerCountdownEnabled->setChecked($isTimerCountdownEnabled);

		$order += 100;
		$timerCountdownDuration = $subForm->createElement('TimerInput', 'timerCountdownDuration', array (
			'label' => 'Countdown Duration',
			'required' => $isTimerCountdownEnabled,
			'step' => 1,
			'min' => '00:00:00',
			'order' => $order,
		),
		array ());

		$timerCountdownDuration->setValueAsNumber((int)$this->getAttributeValue('timerCountdownDuration'));

		$order += 100;
		$timerProceedBeforeTimeout = $subForm->createElement('Checkbox', 'timerProceedBeforeTimeout', array (
			'label' => 'Proceed before Timeout',
			'value' => '1',
			'checked' => false,
			'order' => $order
		), array ());
		$timerProceedBeforeTimeout->setChecked($this->getAttributeValue('timerProceedBeforeTimeout'));

		$order += 100;
		$timerProceedHelp = $subForm->createElement('StaticHtml', 'timerProceedHelp', array (
			'label' => 'Variable Name Help',
			'order' => $order
		), array ());
		$timerProceedHelp->setValue('If the participant is allowed to proceed before timeout, both the elapsed and remaining time will be saved in two variables.<br />Their context will be Participant / Stepgroup Loop.');

		$order += 100;
		$timerProceedTimeVarname = $subForm->createElement('TextInput', 'timerProceedTimeVarname', array('label'=>'Variable Name for Proceed Time (sec)', 'order'=>$order), array());
		$timerProceedTimeVarname->addValidator(new \Sophie_Validate_Session_Variable_Name());
		$timerProceedTimeVarname->setValue($this->getAttributeValue('timerProceedTimeVarname'));

		$order += 100;
		$timerRemainingTimeVarname = $subForm->createElement('TextInput', 'timerRemainingTimeVarname', array('label'=>'Variable Name for Remaining Time (sec)', 'order'=>$order), array());
		$timerRemainingTimeVarname->addValidator(new \Sophie_Validate_Session_Variable_Name());
		$timerRemainingTimeVarname->setValue($this->getAttributeValue('timerRemainingTimeVarname'));

		$order += 100;
		$timerOnTimeoutOptions = array (
			'continue' => 'Continue by going to the next step.',
			'warning' => 'Stay and show warning.'
		);
		$timerOnTimeout = $subForm->createElement('Select', 'timerOnTimeout', array (
			'autoInsertNotEmptyValidator' => false,
			'multiOptions' => $timerOnTimeoutOptions,
			'label' => 'On Timeout',
			'required' => true,
			'order' => $order,
		));
		$timerOnTimeout->setValue($this->getAttributeValue('timerOnTimeout'));

		$order += 100;
		$timerOnTimeoutWarning = $subForm->createElement('TextInput', 'timerOnTimeoutWarning', array (
			'label' => 'On Timeout Warning',
			'order' => $order,
		), array ());
		$timerOnTimeoutWarning->setValue($this->getAttributeValue('timerOnTimeoutWarning'));

		// startup form elements
		$order += 100;
		$timerStartupBlank = $subForm->createElement('StaticHtml', 'timerStartupBlank', array (
			'label' => '',
			'trim' => 'true',
			'order' => $order,
		), array ());
		//$timerStartupBlank->setValue('xxx');

		$order += 100;
		$timerShowOnStartupOptions = array (
			'startupContent' => 'Show startup content',
			'mainContent' => 'Show main content'
		);
		$timerShowOnStartup = $subForm->createElement('Select', 'timerShowOnStartup', array (
			'multiOptions' => $timerShowOnStartupOptions,
			'label' => 'Show On Startup',
			'required' => true,
			'order' => $order,
		));
		$timerShowOnStartup->setValue($this->getAttributeValue('timerShowOnStartup'));

		$order += 100;
		$timerStartupHeadline = $subForm->createElement('TextInput', 'timerStartupHeadline', array (
			'label' => 'Startup Headline',
			'order' => $order,
		), array ());
		$timerStartupHeadline->setValue($this->getAttributeValue('timerStartupHeadline'));

		$order += 100;
		$timerStartupBodyValue = $this->getAttributeValue('timerStartupBody');
		$timerStartupBodyType = 'SwitchCodemirrorWysiwygTextarea';
		$timerStartupBody = $subForm->createElement($timerStartupBodyType, 'timerStartupBody', array (
			'label' => 'Startup Body',
			'order' => $order,
			'toolbar' => new Sophie_Toolbar_CodeMirror_Html(),
		), array ());
		$timerStartupBody->setValue($timerStartupBodyValue);
		$timerStartupBodyCodeMirrorId = 'window.SymbicFormSwitchCodemirrorWysiwygTextarea.instances[\'' . $timerStartupBody->getId() . '\']';
		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'timer-ContentPane\'), \'onShow\', function() { ' . $timerStartupBodyCodeMirrorId . '.refresh(); } );');

		// countdown form elements
		$order += 100;
		$timerCountdownBlank = $subForm->createElement('StaticHtml', 'timerCountdownBlank', array (
			'label' => '',
			'order' => $order
		), array ());
		//$timerCountdownBlank->setValue('xxx');

		$order += 100;
		$timerShowOnCountdownOptions = array (
			'countdownContent' => 'Show countdown content',
			'startupContent' => 'Show startup content',
			'mainContent' => 'Show main content'
		);
		$timerShowOnCountdown = $subForm->createElement('Select', 'timerShowOnCountdown', array (
			'multiOptions' => $timerShowOnCountdownOptions,
			'label' => 'Show On Countdown',
			'required' => true,
			'order' => $order,
		));
		$timerShowOnCountdown->setValue($this->getAttributeValue('timerShowOnCountdown'));

		$order += 100;
		$timerCountdownHeadline = $subForm->createElement('TextInput', 'timerCountdownHeadline', array (
			'label' => 'Countdown Headline',
			'order' => $order
		), array ());
		$timerCountdownHeadline->setValue($this->getAttributeValue('timerCountdownHeadline'));

		$order += 100;
		$timerCountdownBodyValue = $this->getAttributeValue('timerCountdownBody');
		$timerCountdownBodyType = 'SwitchCodemirrorWysiwygTextarea';
		$timerCountdownBody = $subForm->createElement($timerCountdownBodyType, 'timerCountdownBody', array (
			'label' => 'Countdown Body',
			'order' => $order,
			'toolbar' => new Sophie_Toolbar_CodeMirror_Html(),
		), array ());
		$timerCountdownBody->setValue($timerCountdownBodyValue);
		$timerCountdownBodyCodeMirrorId = 'window.SymbicFormSwitchCodemirrorWysiwygTextarea.instances[\''. $timerCountdownBody->getId() . '\']';
		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'timer-ContentPane\'), \'onShow\', function() { ' . $timerCountdownBodyCodeMirrorId . '.refresh(); } );');

		$order += 100;
		$timerCountdownBodyExplain = $subForm->createElement('StaticHtml', 'timerCountdownBodyExplain', array (
			'label' => '',
			'order' => $order
		), array ());
		$timerCountdownBodyExplain->setValue('Use {#countdown#} within body to embed the countdown timer');

		$order += 100;
		$timerInitialLag = $subForm->createElement('IntInput', 'timerInitialLag', array (
			'label' => 'Initial Lag [ms]',
			'min' => 0,
			'required' => false,
			'value' => $this->getAttributeValue('timerInitialLag'),
			'order' => $order,
		));

		$order += 100;
		$timerGracePeriodServer = $subForm->createElement('IntInput', 'timerGracePeriodServer', array (
			'label' => 'Grace Period Server [ms]',
			'min' => 0,
			'required' => false,
			'value' => $this->getAttributeValue('timerGracePeriodServer'),
			'order' => $order,
		));

		$order += 100;
		$timerGracePeriodClient = $subForm->createElement('IntInput', 'timerGracePeriodClient', array (
			'label' => 'Grace Period Client [ms]',
			'min' => 0,
			'required' => false,
			'value' => $this->getAttributeValue('timerGracePeriodClient'),
			'order' => $order,
		));

		// submit button
		$order += 100;
		$submit = $subForm->createElement('submit', 'timerSave', array (
			'label' => 'Save',
			'order' => $order,
			'ignore' => 'true'
		));

		$subForm->addElements(array (
			$timerEnabled,
			$timerDisplay,
			$timerContext,
			$timerStart,
			$timerDuration,
			$timerCountdownEnabled,
			$timerCountdownDuration,
			$timerProceedHelp,
			$timerProceedBeforeTimeout,
			$timerProceedTimeVarname,
			$timerRemainingTimeVarname,
			$timerOnTimeout,
			$timerOnTimeoutWarning,

			$timerStartupBlank,
			$timerShowOnStartup,
			$timerStartupHeadline,
			$timerStartupBody,

			$timerCountdownBlank,
			$timerShowOnCountdown,
			$timerCountdownHeadline,
			$timerCountdownBodyExplain,
			$timerCountdownBody,

			$timerInitialLag,
			$timerGracePeriodServer,
			$timerGracePeriodClient,

			$submit
		));
	}

	public function adminInternalTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subformName = 'internal';
		$subForm = $form->getSubForm($subformName);
		if (is_null($subForm))
		{
			$subForm = $form->createSubForm();
			$subForm->setAttribs(array (
				'legend' => 'Internal',
				'dijitParams' => array (
					'title' => 'Internal',

				),

			));
			$form->addSubForm($subForm, $subformName);
		}

		$order = 100;
		$internalName = $subForm->createElement('TextInput', 'internalName', array (
			'label' => 'Name',
			'required' => true,
			'trim' => 'true',
			'order' => $order
		), array ());
		$step = $this->getContext()->getStep();
		$internalName->setValue($step['name']);

		$order += 100;
		$internalLabel = $subForm->createElement('TextInput', 'internalLabel', array (
			'label' => 'Label',
			'required' => true,
			'trim' => 'true',
			'order' => $order,
		), array ());

		$internalLabelValidator = new \Sophie_Validate_Treatment_Step_Label();
		$internalLabelValidator->treatmentId = $this->getContext()->getTreatmentId();
		$internalLabelValidator->stepId = $this->getContext()->getStepId();
		$internalLabelValidator->setUniqueCheck(true);
		$internalLabel->addValidator($internalLabelValidator);

		$internalLabel->setValue($this->getContext()->getStepLabel());

		$order += 100;
		$internalNotes = $subForm->createElement('TextareaAutosize', 'internalNote', array (
			'label' => 'Notes',
			'trim' => 'true',
			'order' => $order
		), array ());
		$internalNotes->setValue($this->getAttributeValue('internalNote'));

		$order += 100;
		$__tabAnchor = $subForm->createElement('hidden', '__tabAnchor');
		$__tabAnchor->setValue('');

		$order += 100;
		$submit = $subForm->createElement('submit', 'internalSave', array (
			'label' => 'Save',
			'order' => $order,
			'ignore' => 'true'
		));
		$subForm->addElements(array (
			$internalName,
			$internalLabel,
			$internalNotes,
			$__tabAnchor,
			$submit
		));
	}

	public function adminLayoutTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();

		$subformName = 'layout';
		$subForm = $form->getSubForm('layout');
		if (is_null($subForm))
		{
			$subForm = $form->createSubForm();
			$subForm->setAttribs(array (
				'legend' => 'Layout',
				'dijitParams' => array (
					'title' => 'Layout'
				),
			));
			$form->addSubForm($subForm, $subformName);
		}

		$order = 100;
		$layoutAdditionalCSS = $subForm->createElement('CodemirrorTextarea', 'layoutAdditionalCSS', array (
			'label' => 'CSS',
			'trim' => 'true',
			'order' => $order,
			'CodeMirrorMode' => 'text/css'
		), array ());
		$layoutAdditionalCSS->setValue($this->getAttributeValue('layoutAdditionalCSS'));

		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'layout-ContentPane\'), \'onShow\', function() { ' . $layoutAdditionalCSS->getJsInstance() . '.refresh(); } );');

		$order += 100;
		// TODO: warn if selected theme is not available
		$layoutThemeOptions = array (
			'' => 'Use Treatment Setting',
		);
		$themePath = BASE_PATH . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'sophie' . DIRECTORY_SEPARATOR . 'themes';
		foreach (scandir($themePath) as $theme)
		{
			if ($theme == '.' || $theme == '..')
			{
				continue;
			}
			$layoutThemeOptions[$theme] = $theme;
		}
		$layoutTheme = $subForm->createElement('Select', 'layoutTheme', array (
			'autoInsertNotEmptyValidator' => false,
			'multiOptions' => $layoutThemeOptions,
			'label' => 'Theme',
			'required' => true,
			'order' => $order
		));
		$layoutTheme->setValue($this->getAttributeValue('layoutTheme'));

		// TODO: make choice of layout dependent on choice of theme
		// TODO: read layout options from installed themes
		// TODO: warn if selected layout is not available
		$order += 100;
		$layoutDesignOptions = array (
			'' => 'Use Treatment Setting',
		);
		$layoutDesign = $subForm->createElement('Select', 'layoutDesign', array (
			'autoInsertNotEmptyValidator' => false,
			'multiOptions' => $layoutDesignOptions,
			'label' => 'Design',
			'required' => true,
			'order' => $order
		));
		$layoutDesign->setValue($this->getAttributeValue('layoutDesign'));

		$order += 100;
		$submit = $subForm->createElement('submit', 'layoutSave', array (
			'label' => 'Save',
			'order' => $order,
			'ignore' => true
		));
		$subForm->addElements(array (
			$layoutAdditionalCSS,
			$layoutTheme,
			$layoutDesign,
			$submit
		));
	}

	public function adminRuntimeinitializationTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subformName = 'runtimeinitialization';
		$subForm = $form->getSubForm($subformName);
		if (is_null($subForm))
		{
			$subForm = $form->createSubForm();
			$subForm->setAttribs(array (
				'legend' => 'Runtime Initialization',
				'dijitParams' => array (
					'title' => 'Runtime Initialization'
				),

			));
			$form->addSubForm($subForm, $subformName);
		}

		$order = 100;
		$initializationScript = $subForm->createElement('CodemirrorTextarea', 'initializationScript', array (
			'label' => 'Script',
			'trim' => 'true',
			'order' => $order,
			'toolbar' => new Sophie_Toolbar_CodeMirror_Php_Attribute($this->getAttributeConfigurations()),
		), array ());
		$initializationScript->setAttrib('onchange', 'expdesigner.updateStepCodeSanitizerResults(' . $this->getContext()->getStepId() . ', ' . $initializationScript->getJsInstance() . '.getValue(), \'initializationScriptSanitizerMessages\', \'php\');');

		$initializationScript->setValue($this->getAttributeValue('initializationScript'));
		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'runtimeinitialization-ContentPane\'), \'onShow\', function() { ' . $initializationScript->getJsInstance() . '.refresh(); } );');

		$order += 100;
		$initializationScriptValid = $subForm->createElement('StaticHtml', 'initializationScriptValidator', array (
			'label' => '',
			'trim' => 'true',
			'order' => $order
		), array ());

		$initializationScriptSanitizerCheck = true;
		try {
			$sanitizer = $this->getPhpSanitizer();
			$initializationScriptSanitizerCheck = $sanitizer->isValid('<?php ' . $this->getAttributeValue('initializationScript') . ' ?>');
		}
		catch(Exception $e)
		{
			$initializationScriptSanitizerCheck = false;
		}

		$initializationScriptValidContent = '<div id="initializationScriptSanitizerMessages" class="alert alert-danger"';
		if (!$initializationScriptSanitizerCheck)
		{
			$initializationScriptValidContent .= '>';
			$initializationScriptValidContent .= '<strong>Sanitizer Warning</strong><br />';
			$initializationScriptValidContent .= nl2br($view->escape(implode(', ', $sanitizer->getMessages())));
		}
		else
		{
			$initializationScriptValidContent .= ' style="display:none;">';
		}
		$initializationScriptValidContent .= '</div>';
		$initializationScriptValid->setValue($initializationScriptValidContent);

		$order += 100;
		$submit = $subForm->createElement('submit', 'initializationSave', array (
			'label' => 'Save',
			'order' => $order,
			'ignore' => 'true'
		));
		$subForm->addElements(array (
			$initializationScript,
			$initializationScriptValid,
			$submit
		));
	}

	public function adminRunConditionsTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subformName = 'runConditions';
		$subForm = $form->getSubForm($subformName);
		if (is_null($subForm))
		{
			$subForm = $form->createSubForm();
			$subForm->setAttribs(array (
				'legend' => 'Run Conditions',
				'dijitParams' => array (
					'title' => 'Run Conditions',

				),

			));
			$form->addSubForm($subForm, $subformName);
		}

		$order = 100;
		$typeModel = Sophie_Db_Treatment_Type :: getInstance();
		$types = $typeModel->fetchAll('treatmentId = ' . $this->getContext()->getTreatmentId(), 'label');
		$typeOptions = array ();
		foreach ($types as $type)
		{
			$typeOptions[$type->label] = $type->label . ' - ' . $type->name;
		}
		$runConditionStepTypes = $subForm->createElement('ParticipantTypeSelect', 'runConditionStepTypes', array (
			'multiOptions' => $typeOptions,
			'label' => 'Run Condition for Participant Types',
			'trim' => 'true',
			'order' => $order
		));
		$stepTypeModel = Sophie_Db_Treatment_Step_Type :: getInstance();
		$stepTypes = $stepTypeModel->getByStep($this->getContext()->getStepId());
		$selectedStepTypes = array ();
		foreach ($stepTypes as $stepType)
		{
			$selectedStepTypes[] = $stepType['typeLabel'];
		}
		$runConditionStepTypes->setValue($selectedStepTypes);

		$order += 100;
		$runConditionScript = $subForm->createElement('CodemirrorTextarea', 'runConditionScript', array (
			'label' => 'Run Condition Script',
			'order' => $order,
			'toolbar' => new Sophie_Toolbar_CodeMirror_Php(),
		), array ());
		$runConditionScript->setAttrib('onchange', 'expdesigner.updateStepCodeSanitizerResults(' . $this->getContext()->getStepId() . ', ' . $runConditionScript->getJsInstance() . '.getValue(), \'runConditionScriptSanitizerMessages\', \'php\');');
		$runConditionScript->setValue($this->getAttributeValue('runConditionScript'));
		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'runConditions-ContentPane\'), \'onShow\', function() { ' . $runConditionScript->getJsInstance() . '.refresh(); } );');
		$order += 100;

		$runConditionScriptValid = $subForm->createElement('StaticHtml', 'runConditionScriptValidator', array (
			'label' => '',
			'trim' => 'true',
			'order' => $order
		), array ());

		$runConditionsSanitizerCheck = true;
		try {
			$sanitizer = $this->getPhpSanitizer();
			$runConditionsSanitizerCheck = $sanitizer->isValid('<?php ' . $this->getAttributeValue('runConditionScript') . ' ?>');
		}
		catch(Exception $e)
		{
			$runConditionsSanitizerCheck = false;
		}

		$runConditionsValidContent = '<div id="runConditionScriptSanitizerMessages" class="alert alert-danger"';
		if (!$runConditionsSanitizerCheck)
		{
			$runConditionsValidContent .= '>';
			$runConditionsValidContent .= '<strong>Sanitizer Warning</strong><br />';
			$runConditionsValidContent .= nl2br($view->escape(implode(', ', $sanitizer->getMessages())));
		}
		else
		{
			$runConditionsValidContent .= ' style="display:none;">';
		}
		$runConditionsValidContent .= '</div>';
		$runConditionScriptValid->setValue($runConditionsValidContent);

		$order += 100;
		$runConditionDefaultOptions = array (
			'1' => 'Run on Default',
			'0' => 'Do not run on Default'
		);
		$runConditionDefault = $subForm->createElement('Select', 'runConditionDefault', array (
			'multiOptions' => $runConditionDefaultOptions,
			'label' => 'Default Run Condition Script',
			'order' => $order
		), array ());
		$runConditionDefault->setValue($this->getAttributeValue('runConditionDefault'));

		/*
		    $content = '<table width="80%" border="1"> <tr> <th> Field </th> <th> Value </th></tr>';
		    $content .= '<tr><td><b>Run Condition</b></td><td><table>';
		    for ($i = 0; $i <= 10; $i++)
		    {
		      $content .= '<tr><td>';
		      $content .= $view->formSelect('FORM_runCondition' . $i . 'Param1Type', $this->getAttributeValue('runCondition' . $i . 'Param1Type'), null, array(''=>'inactive', 'stepgroupLoop'=>'stepgroupLoop', 'participantVar'=>'participant variable', 'groupVar'=>'group variable', 'globalVar'=>'global variable'));
		      $content .= '</td><td>';
		      $content .= $view->formText('FORM_runCondition' . $i . 'Param1', $this->getAttributeValue('runCondition' . $i . 'Param1'));
		      $content .= '</td><td>';
		      $content .= $view->formSelect('FORM_runCondition' . $i . 'Operator', $this->getAttributeValue('runCondition' . $i . 'Operator'), null, array('empty'=>'empty', '!empty'=>'not empty','==' => '== (equal)', '<>'=>'<> (unequal)', '<'=>'< (lower than)', '>' => '> (higher than)', '<='=>'<= (lower than or equal)', '>=' => '>= (higher than or equal)'));
		      $content .= '</td><td>';
		      $content .= $view->formSelect('FORM_runCondition' . $i . 'Param2Type', $this->getAttributeValue('runCondition' . $i . 'Param2Type'), null, array('value'=>'value', 'stepgroupLoop'=>'stepgroupLoop', 'participantVar'=>'participant variable', 'groupVar'=>'group variable', 'globalVar'=>'global variable'));
		      $content .= '</td><td>';
		      $content .= $view->formText('FORM_runCondition' . $i . 'Param2', $this->getAttributeValue('runCondition' . $i . 'Param2'));
		      $content .= '</td></tr>';
		    }
		    $content .= '</table></td></tr>';
		*/
		$order += 100;
		$submit = $subForm->createElement('submit', 'runConditionSave', array (
			'label' => 'Save',
			'order' => $order,
			'ignore' => 'true'
		));
		$subForm->addElements(array (
			$runConditionStepTypes,
			$runConditionDefault,
			$runConditionScript,
			$runConditionScriptValid,
			$submit
		));
	}

	// Help Tab
	public function adminHelpTabHasContent()
	{
		$helpFile = $this->getBasePath() . DIRECTORY_SEPARATOR . 'adminHelp.html';
		return (file_exists($helpFile));
	}

	public function adminHelpTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subformName = 'help';
		$subForm = $form->getSubForm($subformName);
		if (is_null($subForm))
		{
			$subForm = $form->createSubForm();
			$subForm->setAttribs(array (
				'legend' => 'Help',
				'dijitParams' => array (
					'title' => 'Help',

				),

			));
			$form->addSubForm($subForm, $subformName);
		}

		$help = $subForm->createElement('StaticHtml', 'help', array (), array (
			'disabled' => 'true'
		));
		$help->setValue($this->adminHelpTabGetContent());

		$subForm->addElements(array (
			$help
		));
	}

	public function adminHelpTabGetContent()
	{
		$helpFile = $this->getBasePath() . DIRECTORY_SEPARATOR . 'adminHelp.html';
		if (file_exists($helpFile))
		{
			return file_get_contents($helpFile);
		}

		return 'No Help available';
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////
	// ADMIN FORM VALIDATION
	/////////////////////////////////////////////////////////////////////////////////////////////////

	public function adminIsValid($parameters)
	{
		$form = $this->adminGetForm();
		$formValid = $form->isValid($parameters);

		$tabs = $this->adminGetOrderedTabs();
		foreach ($tabs as $tab)
		{
			$subForm = $form->getSubForm($tab['id']);

			$tabFunction = 'admin' . ucfirst($tab['id']) . 'TabValidate';
			if (!method_exists($this, $tabFunction))
			{
				continue;
			}

			if (!$this->$tabFunction($subForm, $parameters))
			{
				$form->markAsError();
				$formValid = false;
			}
		}
		return $formValid;
	}

	public function adminRunConditionsTabValidate($subForm, $parameters)
	{
		$subFormValid = true;
		return $subFormValid;
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////
	// ADMIN FORM PROCESSING
	/////////////////////////////////////////////////////////////////////////////////////////////////

	public function adminProcess()
	{
		$form = $this->adminGetForm();
		$tabs = $this->adminGetTabs();

		$values = array ();
		foreach ($form->getSubForms() as $subForm)
		{
			$values = array_merge($values, $subForm->getValues());
		}

		foreach ($tabs as $tab)
		{
			$tabFunction = 'admin' . ucfirst($tab['id']) . 'TabProcess';

			if (method_exists($this, $tabFunction))
			{
				$this-> $tabFunction ($values);
			}
		}
	}

	public function adminRunConditionsTabProcess($values)
	{
		$this->setAttributeValue('runConditionScript', $values['runConditionScript']);
		$this->setAttributeValue('runConditionDefault', $values['runConditionDefault']);
		Sophie_Db_Treatment_Step_Type :: getInstance()->setByStep($this->getContext()->getStepId(), $values['runConditionStepTypes']);

		$runConditions = array ();

		$i2 = 1;
		for ($i = 1; $i <= 10; $i++)
		{
			if (isset ($values['runCondition' . $i . 'Param1Type']) && $values['runCondition' . $i . 'Param1Type'] != '')
			{
				$runCondition = array ();

				$this->setAttributeValue('runCondition' . $i . 'Param1Type', $values['runCondition' . $i . 'Param1Type']);
				$this->setAttributeValue('runCondition' . $i . 'Param1', $values['runCondition' . $i . 'Param1']);
				$this->setAttributeValue('runCondition' . $i . 'Operator', $values['runCondition' . $i . 'Operator']);
				$this->setAttributeValue('runCondition' . $i . 'Param2Type', $values['runCondition' . $i . 'Param2Type']);
				$this->setAttributeValue('runCondition' . $i . 'Param2', $values['runCondition' . $i . 'Param2']);

				$runConditions[] = $runCondition;
				$i2++;
			}
		}

		$i = 1;
		foreach ($runConditions as $runCondition)
		{
			$this->setAttributeValue('runCondition' . $i . 'Param1Type', $runCondition[$i . 'Param1Type']);
			$this->setAttributeValue('runCondition' . $i . 'Param1', $runCondition[$i . 'Param1']);
			$this->setAttributeValue('runCondition' . $i . 'Operator', $runCondition[$i . 'Operator']);
			$this->setAttributeValue('runCondition' . $i . 'Param2Type', $runCondition[$i . 'Param2Type']);
			$this->setAttributeValue('runCondition' . $i . 'Param2', $runCondition[$i . 'Param2']);
			$i++;
		}

		for ($i2 = $i; $i <= 10; $i++)
		{
			$this->unsetAttribute('runCondition' . $i . 'Param1Type');
			$this->unsetAttribute('runCondition' . $i . 'Param1');
			$this->unsetAttribute('runCondition' . $i . 'Operator');
			$this->unsetAttribute('runCondition' . $i . 'Param2Type');
			$this->unsetAttribute('runCondition' . $i . 'Param2');
		}
	}

	public function adminRuntimeinitializationTabProcess($values)
	{
		$this->setAttributeValue('initializationScript', $values['initializationScript']);
	}

	public function adminLayoutTabProcess($values)
	{
		$this->setAttributeValue('layoutAdditionalCSS', $values['layoutAdditionalCSS']);
		$this->setAttributeValue('layoutTheme', $values['layoutTheme']);
		$this->setAttributeValue('layoutDesign', $values['layoutDesign']);
	}

	public function adminInternalTabProcess($values)
	{
		if (empty($values['internalLabel']))
		{
			$stepLabel = new Zend_Db_Expr('NULL');
		}
		else
		{
			$stepLabel = $values['internalLabel'];
		}
		Sophie_Db_Treatment_Step :: getInstance()->update(array (
			'name' => $values['internalName'],
			'label' => $stepLabel,
		), 'id =' . $this->getContext()->getStepId());
		$this->setAttributeValue('internalNote', $values['internalNote']);
	}

	public function adminTimerTabProcess($values)
	{
		if ($this->options[ self :: TIMER ][ self :: FORCED ])
		{
			$values['timerEnabled'] = 1;
		}

		if (!$values['timerEnabled'])
		{
			$values['timerOnTimeout'] = 'continue';
		}

		$timerDurationNumber = Symbic_Form_Element_TimeInput::valueToNumber($values['timerDuration']);
		$timerCountdownDurationNumber = Symbic_Form_Element_TimeInput::valueToNumber($values['timerCountdownDuration']);

		$this->setAttributeValue('timerEnabled', $values['timerEnabled']);
		$this->setAttributeValue('timerDisplay', $values['timerDisplay']);
		$this->setAttributeValue('timerContext', $values['timerContext']);
		$this->setAttributeValue('timerStart', $values['timerStart']);
		$this->setAttributeValue('timerDuration', $timerDurationNumber);
		$this->setAttributeValue('timerCountdownEnabled', $values['timerCountdownEnabled']);
		$this->setAttributeValue('timerCountdownDuration', $timerCountdownDurationNumber);
		$this->setAttributeValue('timerProceedBeforeTimeout', $values['timerProceedBeforeTimeout']);
		$this->setAttributeValue('timerProceedTimeVarname', $values['timerProceedTimeVarname']);
		$this->setAttributeValue('timerRemainingTimeVarname', $values['timerRemainingTimeVarname']);
		$this->setAttributeValue('timerOnTimeout', $values['timerOnTimeout']);
		$this->setAttributeValue('timerOnTimeoutWarning', $values['timerOnTimeoutWarning']);

		$this->setAttributeValue('timerShowOnStartup', $values['timerShowOnStartup']);
		$this->setAttributeValue('timerStartupHeadline', $values['timerStartupHeadline']);
		$this->setAttributeValue('timerStartupBody', $values['timerStartupBody']);

		$this->setAttributeValue('timerShowOnCountdown', $values['timerShowOnCountdown']);
		$this->setAttributeValue('timerCountdownHeadline', $values['timerCountdownHeadline']);
		$this->setAttributeValue('timerCountdownBody', $values['timerCountdownBody']);

		$this->setAttributeValue('timerInitialLag', (int)$values['timerInitialLag']);
		$this->setAttributeValue('timerGracePeriodServer', (int)$values['timerGracePeriodServer']);
		$this->setAttributeValue('timerGracePeriodClient', (int)$values['timerGracePeriodClient']);
	}
}