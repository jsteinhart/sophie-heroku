<?php
namespace Expadmin\Form\Session;

class Edit extends Add3
{
	public function init()
	{
		parent::init();

		$this->setLegend('Edit Session');

		$this->removeElement('cacheTreatment');
		$this->removeElement('debugConsole');

		$this->addElement('hidden', 'sessionId');
		$submit = $this->getElement('submit');
		$submit->setLabel('Save');
	}
}