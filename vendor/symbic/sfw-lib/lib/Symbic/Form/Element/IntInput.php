<?php
class Symbic_Form_Element_IntInput extends Symbic_Form_Element_AbstractNumberInput
{
	protected $step		= 1;

	public function getDefaultValidators()
	{
		$validators = parent::getDefaultValidators();
		/*
		This validator does not work as expected:
		$validators['int'] = array(
							'validator'					=> 'int',
							'breakChainOnFailure'		=> true,
							'options'					=> array()
					);*/
		return $validators;

	}
	
	public function setStep($step)
	{
		if (abs(floor($this->step) - $this->step) > 0)
		{
			throw new Exception('Int form element can only have an integer step value');
		}
		return parent::setStep($step);
	}
}