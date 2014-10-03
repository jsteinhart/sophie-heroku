<?php
namespace Sfwinstaller\Installer\Step;

class Confirm extends AbstractStep
{
	public function getForm()
	{
		$form = parent::getForm();

		$form->setLegend('Confirm Installation Options');

		$accept = $form->createElement('checkboxInlineLabel', 'accept');
		$accept->setRequired(true);
		$accept->setAttrib('inlineLabel', 'Please confirm that you want to erase existing tables in the database.');

	    $validator = new \Zend_Validate_NotEmpty();
	    $validator->setMessage(
	        'The installer will not proceed without your confirmation.',
	        \Zend_Validate_NotEmpty::IS_EMPTY
	    );

		$accept->addValidator($validator);
		$form->addElement($accept);
		
		$form->addElement('submit', 'submit', array('label'=>'Send', 'class'=>'btn btn-primary'));

		return $form;
	}

	protected function processValid($form)
	{
		$installRunnerFile = BASE_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'Sfwinstaller.php';

		if(file_exists($installRunnerFile))
		{
			$installRunnerClass = '\Application_Installer_Runner';
			if (!class_exists($installRunnerClass))
			{
				require_once($installRunnerClass);
			}
		}
		else
		{
			$installRunnerClass = '\Sfwinstaller\Installer\Runner\Standard';
		}

		$runner = new $installRunnerClass();
		$runner->run($this->getAllValues());
		$this->clearValues();
		return true;
	}

	protected function render($form)
	{
		$config = $this->getAllValues();
		
		$this->view->config = $config;
		
		if ($config['dbconfig']['populateSchema'] != '1')
		{
			$accept = $form->getElement('accept');
			$accept->setAttrib('inlineLabel', 'Please confirm that you want proceed with the installation.');
		}
		
		$this->view->form = $form;

		echo $this->view->render('index/confirm.phtml');
	}
}