<?php
namespace Expdesigner\Form\Asset;

class Add extends Edit
{
	public function init()
	{
		parent::init();
		
		$this->setLegend('Edit Asset');

		$this->removeElement('content');
		$file = $this->createElement('file', 'data', array (
			'label' => 'File',
			'required' => true,
			'order' => 200,
		));
		/*$file->addValidator('Size', true, array(
				'max'      => 16777215,
				'messages' => array(
					\Zend_Validate_File_Size::TOO_BIG => 'The maximum permitted image file size is %max% - selected image file size is %size%.'
				)
			 ));*/
		$this->addElement($file);

		$submit = $this->getElement('submit');
		$submit->setLabel('Add');
	}
}