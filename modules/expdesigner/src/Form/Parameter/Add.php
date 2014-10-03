<?php
namespace Expdesigner\Form\Parameter;

class Add extends \Symbic_Form_Standard
{

	public function init()
	{
		$this->setLegend('Add Parameter');

		$this->addElement('hidden', 'treatmentId');

		$this->addElement('text', 'name', array (
			'label' => 'Name',
			'required' => true,
			'regExp' => '[a-zA-Z0-9_]+',
			'invalidMessage' => 'Invalid non-space text.',
			'validators' => array (
				'alnum',
				array (
					'StringLength',
					true,
					array (
						null,
						36
					)
				)
			),
			'promptMessage' => 'The name of a parameter must be unique.'
		));

		$this->addElement('text', 'value', array (
			'label' => 'Value',
			'required' => true,
		));

		$this->addElement('submit', 'submit', array (
			'label' => 'save'
		));
	}
}