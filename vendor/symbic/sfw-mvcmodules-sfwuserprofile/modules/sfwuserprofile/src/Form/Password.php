<?php
namespace Sfwuserprofile\Form;

class Password extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Set Password');

		$this->addElement('password', 'oldPassword', array (
			'label' => 'Current Password',
			'required' => true
		));

		$this->addElement('password', 'newPassword', array (
			'label' => 'New Password',
			'required' => true
		));

		$this->addElement('password', 'newPassword2', array (
			'label' => 'Repeat new Password',
			'required' => true,
			'validators' => array(
				array('identical', false, array('token' => 'newPassword'))
			)
		));

		$this->addElement('submit', 'submitPasswordForm', array (
			'label' => 'Set new Password',
			'ignore' => true
		));
	}
}