<?php
namespace Sfwinstaller\Installer\Step;

class License extends AbstractStep
{
	protected function getLicenseFile()
	{
		return BASE_PATH . DIRECTORY_SEPARATOR . 'LICENSE';
	}

	protected function getLicenseFileContents()
	{
		$licenseFile = $this->getLicenseFile();

		if (!file_exists($licenseFile))
		{
			return 'License file not found. Please contact the developer for license information.';
		}
		else
		{
			return file_get_contents($licenseFile);
		}
	}
	
	protected function getForm()
	{
		$form = parent::getForm();
		
		$form->setLegend('License agreement');
		$form->addElement('StaticHtml', 'licenseText',
			array(
				'value' => 'Please read and accept the following licence agreement:<br /><div style="overflow:scroll; height:300px; border:solid 1px #000000;"><pre>' . $this->getLicenseFileContents() . '</pre></div>',
				'ignore' => true
				),
			array()
			);

		$accept = $form->createElement('CheckboxInlineLabel', 'accept');
		$accept->setAttrib('inlineLabel', 'I accept the license agreement');
		$accept->setValue('accept');
		$form->addElement($accept);

		$form->addElement('submit', 'next', array(
				'label'=>'Next',
				'class'=>'btn btn-primary pull-right',
				'ignore' => true
			)
		);

		return $form;
	}

	protected function processValid($form)
	{
		$values = $form->getValues();
		$this->setValues($values);

		$acceptElement = $form->getElement('accept');
		if ($acceptElement->isChecked())
		{
			return true;
		}
		else
		{
			$acceptElement->addError('You need to accept the license before using the application.');
			return false;
		}
	}
}