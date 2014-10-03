<?php
/**
 * SoPHIE Timer API Class
 *
 * The Timer API provides methods to interact with SoPHIE's timer system
 */

class Sophie_Api_Timer_1_0_0_Api extends Sophie_Api_Abstract
{
	public function getMicrotime()
	{
		return ceil(microtime(true) * 1000);
	}

	/* functions to get timer settings */
	public function getInitialLag()
	{
		$timerInitialLag = $this->getContext()->getSteptype()->getAttributeRuntimeValue('timerInitialLag');
		if ($timerInitialLag !== null)
		{
			return (int)$timerInitialLag;
		}

		$config = Zend_Registry::get('config');
		if (!isset($config['systemConfig']) || !isset($config['systemConfig']['sophie']) || !isset($config['systemConfig']['sophie']['expfront']) || !isset($config['systemConfig']['sophie']['expfront']['timerInitialLag']))
		{
			return 1000;
		}

		return (int)$config['systemConfig']['sophie']['expfront']['timerInitialLag'];
	}

	public function getGracePeriodServer()
	{
		$timerGracePeriodServer = $this->getContext()->getSteptype()->getAttributeRuntimeValue('timerGracePeriodServer');
		if ($timerGracePeriodServer !== null)
		{
			return (int)$timerGracePeriodServer;
		}

		$config = Zend_Registry::get('config');
		if (!isset($config['systemConfig']) || !isset($config['systemConfig']['sophie']) || !isset($config['systemConfig']['sophie']['expfront']) || !isset($config['systemConfig']['sophie']['expfront']['timerGracePeriodServer']))
		{
			return 500;
		}

		return (int)$config['systemConfig']['sophie']['expfront']['timerGracePeriodServer'];
	}

	public function getGracePeriodClient()
	{
		$timerGracePeriodClient = $this->getContext()->getSteptype()->getAttributeRuntimeValue('timerGracePeriodClient');
		if ($timerGracePeriodClient !== null)
		{
			return (int)$timerGracePeriodClient;
		}

		$config = Zend_Registry::get('config');
		if (!isset($config['systemConfig']) || !isset($config['systemConfig']['sophie']) || !isset($config['systemConfig']['sophie']['expfront']) || !isset($config['systemConfig']['sophie']['expfront']['timerGracePeriodClient']))
		{
			return 0;
		}

		return (int)$config['systemConfig']['sophie']['expfront']['timerGracePeriodClient'];
	}

	public function isEnabled()
	{
		$timerEnabled = $this->getContext()->getSteptype()->getAttributeRuntimeValue('timerEnabled');
		return $timerEnabled === '1';
	}

	public function isCountdownEnabled()
	{
		$timerEnabled = $this->getContext()->getSteptype()->getAttributeRuntimeValue('timerCountdownEnabled');
		return $timerEnabled === '1';
	}

	public function getTimerStart()
	{
		$timerStart = $this->getContext()->getSteptype()->getAttributeRuntimeValue('timerStart');
		if ($timerStart !== 'sync-context')
		{
			return 'admin';
		}
		return $timerStart;
	}

	public function getTimerContext()
	{
		$context = $this->getContext()->getSteptype()->getAttributeRuntimeValue('timerContext');
		if ($context !== 'G' && $context !== 'P')
		{
			return 'E';
		}
		return $context;
	}

	public function isTimerDisplay()
	{
		$display = $this->getContext()->getSteptype()->getAttributeRuntimeValue('timerDisplay');
		return $display === '1';
	}

	public function getTimerDisplay()
	{
		$display = $this->getContext()->getSteptype()->getAttributeRuntimeValue('timerDisplay');
		return $display;
	}

	public function isTimerProceedBeforeTimeout()
	{
		$proceedBeforeTimeout = $this->getContext()->getSteptype()->getAttributeRuntimeValue('timerProceedBeforeTimeout');
		return $proceedBeforeTimeout === '1';
	}

	public function getTimerProceedBeforeTimeout()
	{
		$proceedBeforeTimeout = $this->getContext()->getSteptype()->getAttributeRuntimeValue('timerProceedBeforeTimeout');
		return $proceedBeforeTimeout;
	}

	public function getTimerShowOnStartup()
	{
		$showOnStartup = $this->getContext()->getSteptype()->getAttributeRuntimeValue('timerShowOnStartup');
		return $showOnStartup;
	}

	public function getTimerShowOnCountdown()
	{
		$showOnCountdown = $this->getContext()->getSteptype()->getAttributeRuntimeValue('timerShowOnCountdown');
		return $showOnCountdown;
	}
	
	public function getTimerVariableContext()
	{
		return $this->getTimerContext() . 'SL';
	}

	public function getTimerVariablePrefix()
	{
		return '__step_' . $this->getContext()->getStepId() . '_timer_';
	}

	public function getTimerOnTimeout()
	{
		return $this->getContext()->getSteptype()->getAttributeRuntimeValue('timerOnTimeout');
	}

	public function getTimerDuration()
	{
		return ($this->getContext()->getSteptype()->getAttributeRuntimeValue('timerDuration') * 1000);
	}

	public function getCountdownDuration()
	{
		if ($this->isCountdownEnabled() === true)
		{
			return ($this->getContext()->getSteptype()->getAttributeRuntimeValue('timerCountdownDuration') * 1000);
		}
		else
		{
			return 0;
		}
	}

	/* functions to handle actual timer */
	public function start($durationSeconds = null, $countdownSeconds = null, $initialLagMicroseconds = null)
	{
		if (is_null($initialLagMicroseconds))
		{
			$initialLagMicroseconds = $this->getInitialLag();
		}

		$startTime = $this->getMicrotime() + $initialLagMicroseconds;

		if ($this->isCountdownEnabled() == 1 || !is_null($countdownSeconds))
		{
			if (!is_null($countdownSeconds))
			{
				$startTime += $countdownSeconds * 1000;
			}
			else
			{
				$startTime += $this->getCountdownDuration();
			}
		}

		if (is_null($durationSeconds))
		{
			$duration = $this->getDuration();
		}
		else
		{
			$duration = $durationSeconds * 1000;
		}

		// always set duration before start time!
		$this->setDuration($duration);
		$this->setStartTime($startTime);
	}

	public function getState()
	{
		$remaining = $this->getRemainingTime();
		if ($remaining === false)
		{
			return 'notstarted';
		}

		$time = $this->getMicrotime();
		if ($time < $this->getStartTime())
		{
			if ($this->isCountdownEnabled() == 1 && $this->getStartTime() - $time <= $this->getCountdownDuration())
			{
				return 'countdown';
			}
			else
			{
				// started but in the future (e.g. initialLag period)
				return 'started';
			}
		}

		if ($remaining > 0)
		{
			return 'running';
		}

		return 'ended';
	}

	public function getGracefulState()
	{
		$startTime = $this->getStartTime();

		if (empty($startTime))
		{
			return 'notstarted';
		}

		$gracePeriod = $this->getGracePeriodServer();
		$time = $this->getMicrotime();

		if ($time < $startTime)
		{
			if ($this->isCountdownEnabled() == 1 && $startTime - $time <= $this->getCountdownDuration())
			{
				return 'countdown';
			}
			else
			{
				// started but in the future (e.g. initialLag period)
				return 'started';
			}
		}

		$endTime = $startTime + $this->getDuration();
		if ($time < $endTime + $gracePeriod)
		{
			return 'running';
		}

		return 'ended';
	}

	public function hasStartTime()
	{
		$startTime = $this->getStartTime();
		if (!empty($startTime))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	protected function setStartTime($startTime)
	{
		$variableApi = $this->getContext()->getApi('variable');
		$setterFunction = 'set' . $this->getTimerVariableContext();
		$varPrefix = $this->getTimerVariablePrefix();

		return $variableApi->$setterFunction($varPrefix . 'start', $startTime);
	}

	public function getStartTime()
	{
		$variableApi = $this->getContext()->getApi('variable');
		$getterFunction = 'get' . $this->getTimerVariableContext();
		$varPrefix = $this->getTimerVariablePrefix();

		return $variableApi->$getterFunction($varPrefix . 'start');
	}

	protected function setDuration($duration)
	{
		$variableApi = $this->getContext()->getApi('variable');
		$setterFunction = 'set' . $this->getTimerVariableContext();
		$varPrefix = $this->getTimerVariablePrefix();

		return $variableApi->$setterFunction($varPrefix . 'duration', $duration);
	}

	public function getDuration()
	{
		// TODO: fallback to steptype attribute duration as default?
		$variableApi = $this->getContext()->getApi('variable');
		$getterFunction = 'get' . $this->getTimerVariableContext();
		$varPrefix = $this->getTimerVariablePrefix();

		$duration = $variableApi->$getterFunction($varPrefix . 'duration');

		if (empty($duration))
		{
				$duration = $this->getTimerDuration();
		}
		return $duration;
	}

	public function getEndTime()
	{
		$startTime = $this->getStartTime();
		if (empty($startTime))
		{
			return false;
		}
		return $startTime + $this->getDuration();
	}

	public function getRemainingTime()
	{
		$startTime = $this->getStartTime();
		if (empty($startTime))
		{
			return false;
		}

		$remaining = $startTime + $this->getDuration() - $this->getMicrotime();
		if ($remaining < 0)
		{
			$remaining = 0;
		}
		return $remaining;
	}
}