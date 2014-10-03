<?php
namespace Expdesigner\Form\Treatment\Import;

class File extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setAttrib('enctype', 'multipart/form-data');

		$this->addElement('text', 'name', array (
			'label' => 'Name'
			));

		$fileElement = $this->createElement('FileInput', 'contentFile', array (
			'label' => 'Content File',
			'required' => true
		));
		$fileElement->addValidator('Count', false, 1);
		$this->addElement($fileElement);

		if (\Symbic_User_Session::getInstance()->hasRight('admin'))
		{
			$this->addElement('CheckboxInlineLabel', 'noChecksumTest', array (
				'inlineLabel' => 'Disable Checksum Test'
			));
		}

		$this->addElement('submit', 'submitFile', array (
			'label' => 'Import'
		));
	}
}