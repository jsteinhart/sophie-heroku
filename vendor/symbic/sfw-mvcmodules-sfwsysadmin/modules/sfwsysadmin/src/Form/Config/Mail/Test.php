<?php
namespace Sfwsysadmin\Form\Config\Mail;

class Test extends \Symbic_Form
{
	public function init()
	{
		$this->setLegend('Send a Testmail');

		$to = $this->createElement('text', 'to');
		$to->setLabel('To Email');
		$to->setRequired(true);
		$to->addValidator(new \Zend_Validate_EmailAddress());
		$this->addElement($to);
		
		$this->addElement('SubmitInput', 'submit', array('ignore' => true, 'label' => 'Submit', 'class'=>'btn btn-primary pull-right'));
	}
}