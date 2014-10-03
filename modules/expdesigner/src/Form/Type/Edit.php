<?php
namespace Expdesigner\Form\Type;

class Edit extends Add
{
	public function init()
	{
		parent::init();

		$this->setLegend('Edit Participant Type');
		$this->addElement('hidden', 'typeLabel');
		
		$label = $this->getElement('label');
		$label->setDescription('When changing the label afterwards keep in mind that it already may have been used e. g. in steps or the group structure! Those references will not work correctly after changing the label.');
	}
}