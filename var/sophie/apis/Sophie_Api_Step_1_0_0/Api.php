<?php
/**
 * SoPHIE Step API Class
 *
 * The Step API provides access to step defintions in a treatment definition.
 */
class Sophie_Api_Step_1_0_0_Api extends Sophie_Api_Abstract
{

	private function getStepTable()
	{
		return Sophie_Db_Treatment_Step::getInstance();
	}

	/**
	 * Get the step definition identified by the step label
	 *
	 * @param string $label
	 * @return array|null
	 */
	public function get($label = '%current%')
	{
		if (is_null($label) || $label === '%current%')
		{
			return $this->getContext()->getStep();
		}

		$label = $this->translateLabel($label);

		$stepTable = $this->getStepTable();
		$step = $stepTable->fetchByLabel($this->getContext()->getTreatmentId(), $label);
		if (is_null($step))
		{
			return null;
		}

		$step = $step->toArray();
		$step['stepgroupLabel'] = $stepTable->fetchStepgroupLabelByStepId($step['id']);
		return $step;
	}

	/**
	 * Get the active setting for a step identified by the step label
	 *
	 * @param string $label Label of the step to get the active setting for. Defaults to %current%
	 * @return boolean|null
	 */
	public function isActive($label = '%current%')
	{
		$step = $this->get($label);

		if (is_null($step))
		{
			return null;
		}

		return $step['active'] === '1';
	}

	/**
	 * Get the step definition identified by the step id
	 *
	 * @param Integer $id
	 * @return array|null
	 */
	public function getById($id)
	{
		$stepTable = $this->getStepTable();

		$step = $stepTable->fetchByTreatmentIdAndId($this->getContext()->getTreatmentId(), $stepId);
		if (is_null($step))
		{
			return null;
		}

		$step = $step->toArray();
		$step['stepgroupLabel'] = $stepTable->fetchStepgroupLabelByStepId($step['id']);
		return $step;
	}

	/**
	 * Translate a step id returning the step label
	 *
	 * @param Integer $id
	 * @return String|null
	 */
	public function getLabelById($stepId)
	{
		$stepTable = $this->getStepTable();

		$step = $stepTable->fetchByTreatmentIdAndId($this->getContext()->getTreatmentId(), $stepId);
		if (is_null($step))
		{
			return null;
		}

		return $step->label;
	}

	/**
	 * Translate a special step label returning the actual label
	 *
	 * @param String $label
	 * @return String|null
	 */
	public function translateLabel($label = '%current%', $stepgroupLabel = '%current%')
	{
		if (is_null($label) || $label === '%current%')
		{
			$label = $this->getContext()->getStepLabel();
		}
		/*elseif ($label === '%previous%')
		{
			// TODO: throw error if $stepgroupLabel != %current%
			$step = $this->getContext()->getStep();
			$stepPosition = $step['position'];
			// TODO: throw error if $stepPosition < 2

			$step = $this->getStepTable()->fetchRowByStepgroupLabelAndPosition($this->getContext()->getTreatmentId(), $this->getContext()->getStepgroupLabel(), $stepPosition - 1);
			$label = $step['label'];
		}
		elseif ($label === '%next%')
		{
			// TODO: throw error if $stepgroupLabel != %current%
			$step = $this->getContext()->getStep();
			$stepPosition = $step['position'];
			$lastStep = $this->getStepTable()->fetchLastRowByStepgroupLabel($this->getContext()->getStepgroupLabel());
			// TODO: throw error if $stepPosition >= $lastStep['position']

			$step = $this->getStepTable()->fetchRowByStepgroupLabelAndPosition($this->getContext()->getTreatmentId(), $this->getContext()->getStepgroupLabel(), $stepPosition - 1);
			$label = $step['label'];
		}
		elseif ($label === '%first%')
		{
			$stepgroupLabel = $this->getContext()->getApi('stepgroup')->translateLabel($stepgroupLabel);
			$step = $this->getStepTable()->fetchRowByStepgroupLabelAndPosition($this->getContext()->getTreatmentId(), $this->getContext()->getStepgroupLabel(), 1);
			$label = $step['label'];
		}
		elseif ($label === '%last%')
		{
			$stepgroupLabel = $this->getContext()->getApi('stepgroup')->translateLabel($stepgroupLabel);
			$step = $this->getStepTable()->fetchLastRowByStepgroupLabel($stepgroupLabel);
			$label = $step['label'];
		}*/

		return $label;
	}

	/**
	 * Set a step attribute runtime value
	 *
	 * @param string $attributeName
	 * @param mixed $attributeValue
	 * @return boolean
	 */
	public function setRuntimeAttribute($attributeName, $attributeValue)
	{
		$steptype = $this->getContext()->getSteptype();
		if (!($steptype instanceof Sophie_Steptype_Abstract))
		{
			trigger_error('Could not set attribute: Could not retrieve valid steptype.', E_USER_WARNING);
			return false;
		}
		return $steptype->setRuntimeAttributeValue($attributeName, $attributeValue);
	}

	/**
	 * Get all step attribute runtime values
	 *
	 * @return Array
	 */
	public function getRuntimeAttributes()
	{
		$steptype = $this->getContext()->getSteptype();
		if (!($steptype instanceof Sophie_Steptype_Abstract))
		{
			trigger_error('Could not get attribute: Could not retrieve valid steptype.', E_USER_WARNING);
			return false;
		}
		return $steptype->getRuntimeAttributeValues();
	}

	/**
	 * Get a step attribute runtime value
	 *
	 * @param string $attributeName
	 * @return mixed
	 */
	public function getRuntimeAttribute($attributeName)
	{
		$steptype = $this->getContext()->getSteptype();
		if (!($steptype instanceof Sophie_Steptype_Abstract))
		{
			trigger_error('Could not get attribute: Could not retrieve valid steptype.', E_USER_WARNING);
			return false;
		}
		return $steptype->getAttributeRuntimeValue($attributeName);
	}

	/**
	 * Reset a step attribute runtime value to the initial configuration value
	 *
	 * @param string $attributeName
	 * @return boolean
	 */
	public function resetRuntimeAttribute($attributeName)
	{
		$steptype = $this->getContext()->getSteptype();
		if (!($steptype instanceof Sophie_Steptype_Abstract))
		{
			trigger_error('Could not get attribute: Could not retrieve valid steptype.', E_USER_WARNING);
			return false;
		}
		return $steptype->setRuntimeAttributeValue($attributeName, $this->getAttribute($attributeName));
	}

	/**
	 * Get all step attribute configuration values
	 *
	 * @return mixed
	 */
	public function getAttributes()
	{
		return $this->getContext()->getStepAttributes();
	}

	/**
	 * Get a step attribute configuration value
	 *
	 * @param string $attributeName
	 * @return mixed
	 */
	public function getAttribute($attributeName)
	{
		return $this->getContext()->getAttributeValue($attributeName);
	}

}