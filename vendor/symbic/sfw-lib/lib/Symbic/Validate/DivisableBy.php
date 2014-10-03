<?php
class Symbic_Validate_DivisableBy extends Zend_Validate_Abstract
{
	const NOT_NUMERIC = 'notNumeric';
	const NOT_DIVISABLE_BY = 'notDivisableBy';
	const NOT_AN_INTEGER = 'notAnInteger';

	protected $_messageTemplates = array(
		self::NOT_NUMERIC => "'%value%' is not a valid number",
		self::NOT_DIVISABLE_BY => "'%value%' is not divisable by '%step%'",
		self::NOT_AN_INTEGER => "'%value%' is not an integer",
	);

	protected $_messageVariables = array(
		'step' => '_step'
	);

	protected $_step;
	protected $_stepPlaces;

	public function __construct($step)
	{
		if ($step instanceof Zend_Config)
		{
			$step = $step->toArray();
		}

		if (is_array($step))
		{
			if (array_key_exists('step', $step))
			{
				$step = $step['step'];
			}
			else
			{
				throw new Zend_Validate_Exception("Missing option 'step'");
			}
		}

		$this->setStep($step);
	}

	public function getStep()
	{
		return $this->_step;
	}

	public function setStep($step)
	{
		if (!is_int($step) && !is_float($step))
		{
			if (!is_numeric($step))
			{
				throw new Exception('Step option value is not numeric');
			}

			if (strpos($step, '.') === false)
			{
				$step = (int)$step;
			}
			else
			{
				$step = (float)$step;
			}
		}

		if ($step === 0)
		{
			throw new Zend_Validate_Exception("Cannot test for divisability by Zero");
		}

		if ($step < 0)
		{
			throw new Zend_Validate_Exception("Divisability test expects step to be defined as a positive number");
		}

		$stepPlaces = 0;
		if (is_float($step))
		{
			$x = strlen((string)$step) - strpos((string)$step, '.');
			if ($x !== false)
			{
				$stepPlaces = $x;
			}
		}

		$this->_step = $step;
		$this->_stepPlaces = $stepPlaces;

		return $this;
	}

	public function isValid($value)
	{
		if (!is_int($value) && !is_float($value))
		{
			if (!is_numeric($value))
			{
				$this->_setValue($value);
				$this->_error(self::NOT_NUMERIC);
				return false;
			}

			if (strpos($value, '.') === false)
			{
				$value = (int)$value;
			}
			else
			{
				$value = (float)$value;
			}
		}

		$this->_setValue($value);

		$value = abs($value);

		if ($value == 0)
		{
			return true;
		}

		if (is_int($value) && is_int($this->_step))
		{
			if ($value % $this->_step === 0)
			{
				return true;
			}

			if ($this->_step === 1)
			{
				$this->_error(self::NOT_AN_INTEGER);
			}
			else
			{
				$this->_error(self::NOT_DIVISABLE_BY);
			}
			return false;
		}

		$value = $value * pow(10, $this->_stepPlaces);
		if ($value - floor($value) > 0)
		{
			$this->_error(self::NOT_DIVISABLE_BY);
			return false;
		}

		$step = $this->_step * pow(10, $this->_stepPlaces);
		if ((int)$value % (int)$step === 0)
		{
			return true;
		}

		$this->_error(self::NOT_DIVISABLE_BY);
		return false;
	}
}