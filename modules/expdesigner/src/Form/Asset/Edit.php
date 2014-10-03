<?php
namespace Expdesigner\Form\Asset;

class Edit extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Edit Asset');

		$this->addElement('text', 'label', array (
			'label' => 'Label',
			'required' => false,
			'order' => 100,
			'description' => 'The Label must be unique within a treatment.',
		));

		$this->addElement('StaticHtml', 'content', array (
			'label' => 'Content',
			'value' => '- No Preview available -',
			'required' => false,
			'order' => 200,
		));

		$this->addElement('text', 'contentType', array (
			'label' => 'Content-type',
			'value' => '- auto -',
			'required' => false,
			'order' => 300,
		));

		$this->addElement('TextareaAutosize', 'comment', array(
			'label'=>'Comment',
			'order' => 400,
		));

		$this->addElement('submit', 'submit', array (
			'label' => 'Save',
			'ignore' => true,
			'order' => 500,
		));

		$this->addElement('hidden', 'treatmentId', array (
			'required' => true,
			'order' => 600,
		));
	}
}