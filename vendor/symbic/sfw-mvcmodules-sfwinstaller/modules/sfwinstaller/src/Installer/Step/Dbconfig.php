<?php
namespace Sfwinstaller\Installer\Step;

class Dbconfig extends AbstractStep
{
	public function getForm()
	{
		$form = parent::getForm();
		$form->setLegend('Database Configuration');

		$info = $form->createElement('StaticHtml', 'info', array(
				'value' => 'The application uses a MySQL database system. Please enter the user credentials and the databse name the application will use to login to a MySQL database server.<br /><br />',
				'label' => '',
				'ignore' => true
			)
		);
		$form->addElement($info);

		$hostname = $form->createElement('text', 'host');
		$hostname->setLabel('Hostname');
		$hostname->setRequired(true);
		$hostname->setValue('localhost');
		$hostname->addValidator(new \Zend_Validate_Hostname(\Zend_Validate_Hostname::ALLOW_ALL));
		$form->addElement($hostname);

		$username = $form->createElement('text', 'username');
		$username->setLabel('Username');
		$username->setRequired(true);
		$username->setValue('root');
		$form->addElement($username);

		$password = $form->createElement('password', 'password');
		$password->setLabel('Password');
		$password->setRequired(false);
		$form->addElement($password);

		$database = $form->createElement('text', 'dbname');
		$database->setLabel('Database');
		$database->setRequired(true);
		$database->setValue('');
		$form->addElement($database);

		$populateSchema = $form->createElement('checkboxInlineLabel', 'populateSchema');
		$populateSchema->setAttrib('inlineLabel', 'Initalize application database schema (This will ERASE existing data in the database!)');
		$populateSchema->setChecked(true);
		$form->addElement($populateSchema);

		/*$populateData = $form->createElement('checkboxInlineLabel', 'populateData');
		$populateData->setAttrib('inlineLabel', 'Load sample data into the database');
		$populateData->setValue('1');
		$form->addElement($populateData);*/

		$form->addElement('submit', 'submit', array('label'=>'Next', 'class'=>'btn btn-primary pull-right'));
		return $form;
	}
	
	public function processValid($form)
	{
		$values = $form->getValues();

		$failed = false;
		$infoElement = $form->getElement('info');
		
		try {
			// TODO: quote or filter host parameter
			$db = new \PDO('mysql:host=' . $values['host'], $values['username'], $values['password']);
		}
		catch(Exception $e)
		{
			$infoElement->addError('Connection failed: ' . $e->getMessage());
			$failed = true;
		}

		if (!$failed)
		{
			try {
				$db = new \Zend_Db_Adapter_Pdo_Mysql($values);
			}
			catch(Exception $e)
			{
				$infoElement->addError('Database error: ' . $e->getMessage());
				$failed = true;
			}
		}

		// TODO: check if database contains existing tables?
		// TODO: run updates instead of reinstall
		
		if (!$failed)
		{
			$this->setValues($values);
			return true;
		}
		
		return false;
	}
}