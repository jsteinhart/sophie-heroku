<?php
abstract class Symbic_Form_Element_AbstractNumberInput extends Symbic_Form_Element_AbstractInput
{
	public $type = 'number';

	public function getDefaultValidators()
	{
		$validators = array();

		if (!empty($this->step) && $this->step !== 'any')
		{
			$validators['divisableBy'] = array(
								'validator'					=> 'divisableBy',
								'breakChainOnFailure'		=> true,
								'options'					=> array( 'step' => $this->step )
							);
		}
		
		if (!empty($this->min) && !empty($this->max))
		{
			$validators['between'] = array(
				'validator'					=> 'between',
				'breakChainOnFailure'		=> true,
				'options'					=> array(
					'inclusive' 	=> true,
					'min'			=> $this->min,
					'max'			=> $this->max
				)
			);
		}
		elseif (!empty($this->min))
		{
			$validators['greaterOrEqual'] = array(
				'validator'					=> 'greaterOrEqual',
				'breakChainOnFailure'		=> true,
				'options'					=> array(
					'min'			=> $this->min,
				)
			);
		}
		elseif  (!empty($this->max))
		{
			$validators['lessOrEqual'] = array(
				'validator'					=> 'lessOrEqual',
				'breakChainOnFailure'		=> true,
				'options'					=> array(
					'max'			=> $this->max,
				)
			);
		}
		return $validators;
	}

	public function setMin($min)
	{
		// TODO: update validators?
		$this->min = $min;
		return $this;
	}

	public function getMin()
	{
		return $this->min;
	}

	public function setMax($max)
	{
		// TODO: update validators?
		$this->max = $max;
		return $this;
	}

	public function getMax()
	{
		return $this->max;
	}

	public function setStep($step)
	{
		// TODO: update validators?
		$this->step = $step;
		return $this;
	}

	public function getStep()
	{
		return $this->step;
	}
}