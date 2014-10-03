<?php
namespace Expadmin\Form\Session;

class Add3 extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->addElement('text', 'name', array('label'=>'Name', 'required'=>true));

		$this->addElement('TextareaAutosize', 'description', array('label'=>'Description'));

		$this->addElement('select', 'ownerId', array (
			'label' => 'Owner',
			'multiOptions' => $this->getUserAccessOptions(),
			'required' => true
		));

		$userAccessOptions = $this->getUserAccessOptions();
		if (sizeof($userAccessOptions) > 1)
		{
			$this->addElement('Multiselect', 'userAccess', array (
				'label'				=> 'User Access',
				'multiOptions'		=> $userAccessOptions,
			));
		}

		$usergroupAccessOptions = $this->getUsergroupAccessOptions();
		if (sizeof($usergroupAccessOptions) > 0)
		{
			$this->addElement('Multiselect', 'usergroupAccess', array (
				'label'				=> 'Usergroup Access',
				'multiOptions'		=> $usergroupAccessOptions,
			));
		}

		$this->addElement('CheckboxInlineLabel', 'cacheTreatment', array(
			'inlineLabel' => 'Activate Treatment Caching',
			'label' 			=> '',
		));

		/*
		$this->addElement('staticHtml', 'cacheTreatment_info', array(
			'ignore' => true,
			'value' => 'description for cache treatment'
		));
		*/
		$this->addElement('CheckboxInlineLabel', 'debugConsole', array(
			'inlineLabel' => 'Activate Debug Console',
			'label' 			=> '',
		));
		/*
		$this->addElement('staticHtml', 'debugConsole_info', array(
			'ignore' => true,
			'value' => 'description for debug Console'
		));
		*/

		$this->addElement('submit', 'submit', array('label' => 'Add session'));
	}

	public function getUserAccessOptions()
	{
		$options = array();
		$userModel = \System_Db_User::getInstance();
		$users = $userModel->fetchAll(null, 'name');
		foreach ($users as $user)
		{
			$options[$user->id] = $user->name;
		}
		return $options;
	}

	public function getUsergroupAccessOptions()
	{
		$options = array();
		$usergroupModel = \System_Db_Usergroup::getInstance();
		$usergroups = $usergroupModel->fetchAll(null, 'name');
		foreach ($usergroups as $usergroup)
		{
			$options[$usergroup->id] = $usergroup->name;
		}
		return $options;
	}
}