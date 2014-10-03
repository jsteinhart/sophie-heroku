<?php
namespace Expdesigner\Form\Experiment;

class Add extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Add Experiment');

		$this->addElement('text', 'name', array (
			'label' => 'Name',
			'required' => true
		));

		$this->addElement(
			'TextareaAutosize',
			'description',
			array(
				'label' => 'Description'
			)
		);

		$userAccessOptions = $this->getUserAccessOptions();
		$this->addElement('select', 'ownerId', array (
			'label' => 'Owner',
			'multiOptions' => $userAccessOptions,
			'required' => true,
		));

		if (sizeof($userAccessOptions) > 1)
		{
			$this->addElement('Multiselect', 'userAccess', array (
					'label'				=> 'User Access',
					'multiOptions'		=> $userAccessOptions,
				)
			);
		}

		$usergroupAccessOptions = $this->getUsergroupAccessOptions();
		if (sizeof($usergroupAccessOptions) > 0)
		{
			$this->addElement('Multiselect', 'usergroupAccess', array (
					'label'				=> 'Usergroup Access',
					'multiOptions'		=> $usergroupAccessOptions,
				)
			);
		}

		$this->addElement('submit', 'submit', array (
			'label' => 'Add',
			'ignore' => true,
		));

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