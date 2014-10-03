<?php
namespace Expadmin\Form\Options;

class Adminsync extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Set Sync');

		$this->addElement('submit', 'submit', array (
			'label' => 'Set sync now'
		));
	}
}