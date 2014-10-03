<?php
class Sophie_Session_Log_Entry
{

	private $allowed = array(
		'groupLabel' => true,
		'participantLabel' => true,
		'stepgroupLabel' => true,
		'stepgroupLoop' => true,
		'stepLabel' => true,
		'content' => true,
		'contentId' => true,
		'type' => true,
		'details' => true,
		'data' => true,
	);
	private $allowedTypes = array(
		'error' => true,
		'warning' => true,
		'notice' => true,
		'debug' => true,
		'event' => true,
	);

	// default values:
	public $sessionId = null;
	public $groupLabel = null;
	public $participantLabel = null;
	public $stepgroupLabel = null;
	public $stepgroupLoop = null;
	public $stepLabel = null;
	public $content = '';
	public $contentId = null;
	public $type = 'notice';
	public $details = null;
	public $data = null;

	public function __construct($sessionId)
	{
		if (is_null($sessionId))
		{
			throw new Exception('Sophie_Session_Log_Entry must be initialized with a sessionId');
		}
		$this->sessionId = $sessionId;
	}

	public function setContext($context)
	{
		if (!($context instanceof Sophie_Context))
		{
			throw new Exception('Invalid Sophie_Context given for Sophie_Session_Log_Entry :: setContext');
		}
		$this->sessionId = $context->getSessionId();
		
		// groupLabel:
		try
		{
			$stepgroup = $context->getStepgroup();
			$this->groupLabel = ($stepgroup['grouping'] == 'inactive')
				? null
				: $context->getGroupLabel();
		}
		catch (Exception $e)
		{
			$this->groupLabel = null;
		}
		// participantLabel:
		try
		{
			$this->participantLabel = $context->getParticipantLabel();
		}
		catch (Exception $e)
		{
			$this->participantLabel = null;
		}
		// stepgroupLabel:
		try
		{
			$this->stepgroupLabel = $context->getStepgroupLabel();
		}
		catch (Exception $e)
		{
			$this->stepgroupLabel = null;
		}
		// stepgroupLoop:
		try
		{
			$this->stepgroupLoop = $context->getStepgroupLoop();
		}
		catch (Exception $e)
		{
			$this->stepgroupLoop = null;
		}
		// stepLabel:
		try
		{
			$this->stepLabel = $context->getStepLabel();
		}
		catch (Exception $e)
		{
			$this->stepLabel = null;
		}
	}

	public function __set($name, $value)
	{
		if (!isset($this->allowed[$name]))
		{
			throw new Exception('Sophie_Session_Log_Entry does not allow to set ' . $name);
		}

		if ($name == 'type' && !isset($this->allowedTypes[$value]))
		{
			throw new Exception('Sophie_Session_Log_Entry does not allow type "' . $value . '"');
		}
		
		if ($name == 'contentId' && strlen($value) > 64)
		{
			throw new Exception('Sophie_Session_Log_Entry\'s contentId must not be longer than 64 characters');
		}

		$this->$name = $value;
	}

}