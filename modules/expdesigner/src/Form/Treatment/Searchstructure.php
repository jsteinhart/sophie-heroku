<?php
namespace Expdesigner\Form\Treatment;

class Searchstructure extends \Symbic_Form_Standard
{
	private $steptypesMultiSelect = null;
	private $attributesMultiSelect = null;
	
	public function init()
	{
		$this->setLegend('Search Structure');

		$this->addElement('text', 'query', array (
			'label' => 'Query',
			'required' => true
		));

		$this->steptypesMultiSelect = $this->createElement('Multiselect', 'steptypes[]', array (
			'label' => 'Steptypes',
			'required' => true,
			'size' => 5
		));
		$this->addElement($this->steptypesMultiSelect);

		$this->attributesMultiSelect = $this->createElement('Multiselect', 'attribs[]', array (
			'label' => 'Attributes',
			'required' => true,
			'size' => 5
		));
		$this->addElement($this->attributesMultiSelect);

		$this->addElement('submit', 'submit', array (
			'label' => 'Search Steps'
		));
	}
	
	public function setSteptypes($steptypes = array())
	{
		$this->initMultiSelect('steptypesMultiSelect', $steptypes);
	}

	public function setAttributes($eavs = array())
	{
		$this->initMultiSelect('attributesMultiSelect', $eavs);
	}
	
	private function initMultiSelect($MultiSelectLocalName, $data = array())
	{
		if (!is_array($data))
		{
			return false;
		}
		$this->$MultiSelectLocalName->clearMultiOptions();
		$this->$MultiSelectLocalName->addMultiOptions($data);
		$this->$MultiSelectLocalName->setValue(array_keys($data));
		
		$size = count($data);
		if ($size > 5)
		{
			$size = min(15, max(5, ceil($size / 2)));
		}
		$this->$MultiSelectLocalName->setAttrib('size', $size);
	}
}