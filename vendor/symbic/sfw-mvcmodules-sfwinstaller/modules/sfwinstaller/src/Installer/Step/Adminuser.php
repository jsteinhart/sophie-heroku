<?php
namespace Sfwinstaller\Installer\Step;

class Adminuser extends AbstractStep
{
	public function getForm()
	{
		$form = parent::getForm();
		
		$form->setLegend('Admin User Contact Info and Credentials');

		$name = $form->createElement('text', 'name');
		$name->setLabel('Name');
		$name->setRequired(true);
		$name->setValue('Administrator');
		$form->addElement($name);

		$email = $form->createElement('text', 'email');
		$email->setLabel('E-Mail');
		$email->setRequired(true);
		$email->addValidator(new \Zend_Validate_EmailAddress());
		$form->addElement($email);

		$dbconfig = $this->getValues('dbconfig');
		if ($dbconfig['populateSchema'] === '1')
		{
			$skip = $form->createElement('hidden', 'skip');
			$skip->setValue('0');
			$skip->setRequired(true);
			$form->addElement($skip);
		}
		else
		{
			$skip = $form->createElement('checkboxInlineLabel', 'skip');
			$skip->setAttrib('inlineLabel', 'Skip adding an admin user');
			$skip->setRequired(true);
			$form->addElement($skip);
		}

		$username = $form->createElement('text', 'username');
		$username->setLabel('Username');
		$username->setRequired(true);
		$username->setValue('admin');
		$form->addElement($username);

		$password = $form->createElement('password', 'password');
		$password->setLabel('Password');
    	$password->addValidator(new \Zend_Validate_StringLength(array('min' => '6')));
		$password->setRequired(true);
		$form->addElement($password);

		$password2 = $form->createElement('password', 'password2');
		$password2->setLabel('Password (repeat)');
		$password->addValidator(new \Zend_Validate_Identical(array('token' => 'password')));
		$password2->setIgnore(true);
		$password2->setRequired(true);
		$form->addElement($password2);

		$form->addElement('submit', 'submit', array('label'=>'Next', 'class'=>'btn btn-primary pull-right'));

		return $form;
	}

	protected function processForm($form)
	{
		if (isset($_POST['skip']) && $_POST['skip'] == '1')
		{
			$name = $form->getElement('name');
			$email = $form->getElement('email');
			
			if (!isset($_POST['name']))
			{
				$_POST['name'] = '';
			}

			if (!isset($_POST['email']))
			{
				$_POST['email'] = '';
			}

			if ($name->isValid($_POST['name']) && $email->isValid($_POST['email']))
			{
				$this->setValues(array('skip' => '1', 'name' => $_POST['name'], 'email' => $_POST['email']));
				return true;
			}
			
			return false;
		}
			
		if ($form->isValid($_POST))
		{
			if ($this->processValid($form) === true)
			{
				return true;
			}
		}
		return false;
	}
}