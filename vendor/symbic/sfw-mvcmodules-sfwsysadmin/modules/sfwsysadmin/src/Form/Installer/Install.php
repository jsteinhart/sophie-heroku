<?php
namespace Sfwsysadmin\Form\Installer;

class Install extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Run Install');

		$this->addElement('Select', 'operation', array (
			'label' => 'Operation',
			'required' => true,
			'multiOptions' => array (
				'cleaninstall' => 'Clean Installation',
				'install' => 'Installation'
			)
		));

		$this->addElement('SubmitInput', 'submit', array (
			'label' => 'Run'
		));
	}
}