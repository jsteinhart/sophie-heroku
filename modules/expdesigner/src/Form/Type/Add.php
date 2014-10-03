<?php
namespace Expdesigner\Form\Type;

class Add extends \Symbic_Form_Standard
{

	public function init()
	{
		$this->setLegend('Add Participant Type');

		$this->addElement('hidden', 'treatmentId');

		$this->addElement('text', 'name', array (
			'label' => 'Name',
			'required' => true,
		));

		$this->addElement('text', 'label', array (
			'label' => 'Label',
			'required' => true,
			'maxlength' => 2,
			'showMaxlength' => true,
		));
		
		$this->addElement('TextareaAutosize', 'description', array (
			'label' => 'Description',
		));
		
		$icons = array(
			'user.png' => 'Blue User',
			'user_red.png' => 'Red User',
			'user_green.png' => 'Green User',
			'user_orange.png' => 'Orange User',
			'user_gray.png' => 'Gray User',
			'user_suit.png' => 'Suit User',
			'user_female.png' => 'Female User',
			'tux.png' => 'Tux'
		);

		$this->addElement('select', 'icon', array (
			'label' => 'Icon',
			'multiOptions' => $icons,
			'registerInArrayValidator' => false
		));

		$this->addElement('submit', 'submit', array (
			'label' => 'save'
		));
	}
}