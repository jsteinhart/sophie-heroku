<?php
namespace Expfront\Form\Login;

class Code extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setMethod('GET');

		$this->setLegend('Login to experiment');

		$this->setDescription('Please type in the participant code to login to an experiment.');

		$this->addElement('text', 'participantCode', array (
			'label' => 'Participant code',
			'required' => true,
			'autocomplete' => false
			//'pattern' => '[\w]+',
			//'invalidMessage' => 'Invalid non-space text.'
		));

		$this->addElement('hidden', 'participantExternalData');
		
		$this->addElement('submit', 'submit', array (
			'label' => 'Submit Login'
		));

	}
}