<?php
namespace Sfwsysadmin\Form\Installer;

class Installsvn extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Run Install SVN');

		$this->addElement('TextInput', 'svnUsername', array (
			'label' => 'SVN Username',
			'required' => true
		));

		$this->addElement('PasswordInput', 'svnPassword', array (
			'label' => 'SVN Password',
			'required' => true
		));
		
		$this->addElement('Select', 'operation', array (
			'label' => 'Operation',
			'required' => true,
			'multiOptions' => array (
				'cleaninstall' => 'Clean Installation',
				'install' => 'Installation',
				'svnonly' => 'SVN Update only'
			)
		));

		$this->addElement('Select', 'force', array (
			'label' => 'Force Re-Run',
			'required' => true,
			'multiOptions' => array (
				'donotforce' => 'Do not force',
				'force' => 'Force'
			)
		));

		$this->addElement('SubmitInput', 'submit', array (
			'label' => 'Run'
		));
	}
}