<?php
class Sfwinstaller_IndexController extends Symbic_Controller_Action
{
	protected function gotoStep($number)
	{
		$this->_redirect($this->view->url(array('step' => $number), 'sfwinstaller', true));
	}

	public function indexAction()
	{

		$session = new Zend_Session_Namespace('sfwinstaller');
		$moduleConfig = $this->getModule()->getModuleConfig();

		$steps = $moduleConfig['steps'];
		$stepsCount = sizeof($steps);

		if (file_exists(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'application.php'))
		{
			$this->_redirect($this->view->url(array('action' => 'end'), 'sfwinstaller', true));
			return;
		}

		// route to installer page functions
		if (empty($session->finishedStep))
		{
			$session->finishedStep = 1;
		}
		
		$stepNumber = (int)$this->getParam('step', 1);
		if ($stepNumber < 1)
		{
			$stepNumber = 1;
		}
		elseif ($stepNumber > $session->finishedStep)
		{
			$stepNumber = $session->finishedStep;
		}
		
		if ($session->finishedStep > $stepsCount)
		{
			$stepNumber = $session->finishedStep = $stepsCount;
		}

		$currentStep = $steps[$stepNumber - 1];

		$step = new $currentStep['class']($this->getRequest(), $this->view, $session);

		// process the current step: returns true when finished
		if($step->process() === true)
		{
			// for last step redirect to end page
			if ($stepsCount == $stepNumber)
			{
				$this->_forward('end');
				return;
			}
			else
			{
				// if this is not the last step, goto the next one
				if ($session->finishedStep === $stepNumber)
				{
					$session->finishedStep++;
				}
				$this->gotoStep($stepNumber + 1);
				return;
			}
		}

		$this->view->steps = $steps;
		$this->view->stepNumber = $stepNumber;
		$this->view->finishedStep = $session->finishedStep;

		// set custom application installer layout
		$alternativeSfwinstallerPath = APPLICATION_PATH .'/layouts/scripts';
		if (file_exists($alternativeSfwinstallerPath . '/sfwinstaller.phtml'))
		{
			$layout = $this->_helper->layout;
			$layout->setLayoutPath($alternativeSfwinstallerPath);
			$layout->setLayout('sfwinstaller');
		}
		
		$this->_helper->viewRenderer->setNoRender(TRUE);
	}

	public function endAction()
	{
	}
}