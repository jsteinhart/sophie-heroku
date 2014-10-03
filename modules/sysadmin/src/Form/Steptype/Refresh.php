<?php
namespace Sysadmin\Form\Steptype;

class Refresh extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Refresh Steptypes');

		$this->addElement('select', 'procedure', array (
			'label' => 'Refresh Procedure',
			'required' => true,
			'multiOptions' => array (
				'update' => 'Scan for new and updated steptypes',
				'updateAndPurge' => 'Scan for new and updated steptypes and delete steptypes with packages not beeing present',
				'purgeAndReload' => 'Purge existing steptype configs and reload all steptypes from packages'
			)
		));

		$this->addElement('submit', 'submit', array (
			'label' => 'Add'
		));
	}
}