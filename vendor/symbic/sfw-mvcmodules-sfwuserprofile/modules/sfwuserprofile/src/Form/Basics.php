<?php
namespace Sfwuserprofile\Form;

class Basics extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Basic Information');

		$this->addElement('text', 'name', array (
			'label' => 'Name',
			'required' => true
		));

		$this->addElement('text', 'email', array (
			'label' => 'E-Mail',
    		'validators' => array(
    			array('EmailAddress')
    		)
		));

		$this->addElement('submit', 'submitBasicsForm', array (
			'label' => 'Save Changes to Basic Information',
			'ignore' => true
		));
	}
}