<?php
class Symbic_Form_Element_FloatInput extends Symbic_Form_Element_AbstractNumberInput
{
	public $step		= 'any';

	public function getDefaultValidators()
	{
		$validators = parent::getDefaultValidators();
		$validators['float'] = array(
									'validator'					=> 'float',
									'breakChainOnFailure'		=> true,
									'options'					=> array()
								);

		return $validators;
	}	
}