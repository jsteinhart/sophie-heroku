<?php
namespace Sfwsysadmin\Form\User;

class Add extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Add User');

		$this->addElement('text', 'name', array (
			'label' => 'Name',
			'required' => true,
			'filters'    => array('StringTrim'),
		));

		$this->addElement('text', 'login', array (
			'label' => 'Login',
			'required' => true,
			'filters'    => array('StringTrim'),
		));

		$this->addElement('text', 'email', array (
			'label' => 'E-Mail',
    		'validators' => array(
    			array('EmailAddress')
    		),
			'filters'    => array('StringTrim'),
		));

		$generatePassword = $this->createElement('StaticHtml', 'generatePassword', array('label'=>'', 'ignore'=> true, 'required' => false), array());
		$generatePassword->setValue('<small><a href="javascript:sfwsysadmin.userPlaceGeneratedPassword(\'password\', \'password2\', \'showGeneratedPassword\');">Generate Password</a><span id="showGeneratedPassword"></span></small>');
		$this->addElement($generatePassword);
		
		$this->addElement('password', 'password', array (
			'label' => 'Password',
			'required' => true,
			'validators' => array(
				array('identical', false, array('token' => 'password2'))
			)
		));

		$this->addElement('password', 'password2', array (
			'label' => 'Repeat Password',
			'required' => true
		));

		$this->addElement('Select', 'role', array (
			'label' => 'User Role',
			'multiOptions' => array(),
			'required' => true
		));

		$this->addElement('Multiselect', 'usergroups', array (
				'label' => 'Groups',
				'multiOptions' => array(),
				'required' => false
		));

		$this->addElement('checkbox', 'active', array (
			'label' => 'Active User',
		    'required' => false,
			'value' => '1'
		));

		$this->addElement('checkbox', 'sendMessage', array (
			'label' => 'Send Message to User',
			'required' => false
		));

		$this->addElement('submit', 'submit', array (
			'label' => 'Add',
			'ignore' => true
		));
	}
}