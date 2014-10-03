<?php
/**
 * SoPHIE Stepgroup API Class
 *
 * The Stepgroup API provides access to stepgroup defintions in a treatment definition.
 */
class Sophie_Api_Stepgroup_1_0_0_Api extends Sophie_Api_Abstract
{

	private function getStepgroupTable()
	{
		return Sophie_Db_Treatment_Stepgroup::getInstance();
	}

	/**
	 * Get the stepgroup definition identified by the stepgroup label
	 *
	 * @param string $label
	 * @return array|null
	 */
	public function get($label = '%current%')
	{
		if (is_null($label) || $label === '%current%')
		{
			return $this->getContext()->getStepgroup();
		}

		$label = $this->translateLabel($label);
		$stepgroup = $this->getStepgroupTable()->fetchByLabel($this->getContext()->getTreatmentId(), $label);
		if (!is_null($stepgroup)) {
			$stepgroup = $stepgroup->toArray();
		}
		return $stepgroup;
	}

	/**
	 * Get number of loops for a stepgroup identified by the stepgroup label
	 *
	 * @param string $label Label of the stepgroup to get the loop number for. Defaults to %current%
	 * @return Integer|null
	 */
	public function getLoops($label = '%current%')
	{
		$stepgroup = $this->get($label);
		if (!is_null($stepgroup)) {
			return $stepgroup['loop'];
		}
		return null;
	}

	/**
	 * Translate a special stepgroup label returning the actual label
	 *
	 * @param String $stepgroupLabel
	 * @return String|null
	 */
	public function translateLabel($label = '%current%')
	{
		if (is_null($label) || $label === '%current%')
		{
			$label = $this->getContext()->getStepgroupLabel();
		}
		/*elseif ($label === '%previous%')
		{
			$stepgroup = $this->getContext()->getStepgroup();
		}
		elseif ($label === '%next%')
		{
			$stepgroup = $this->getContext()->getStepgroup();

		}
		elseif ($label === '%first%')
		{
		}
		elseif ($label === '%last%')
		{
		}*/

		return $label;
	}

	/**
	 * Translate a special stepgroup loop returning the actual loop
	 *
	 * @param String|Integer $stepgroupLoop
	 * @return Integer|null
	 */
	public function translateLoop($loop = '%current%', $stepgroupLabel = '%current%')
	{
		if (is_null($loop) || $loop === '%current%')
		{
			// TODO: throw error if $stepgroupLabel != %current%
			$loop = $this->getContext()->getStepgroupLoop();
		}
		/*elseif ($label === '%previous%')
		{
			// TODO: throw error if $stepgroupLabel != %current%
			$loop = $this->getContext()->getStepgroupLoop();
			// TODO: if $loop < 2 throw error
			$loop--;
		}
		elseif ($label === '%next%')
		{
			// TODO: throw error if $stepgroupLabel != %current%
			$loop = $this->getContext()->getStepgroupLoop();
			$maxLoops = $this->getLoops($stepgroupLabel);
			// TODO: if $loop >= $maxLoops throw error
			$loop++;
		}
		elseif ($label === '%first%')
		{
			$loop = 1;
		}
		elseif ($label === '%last%')
		{
			$loop = $this->getContext()->getApi('stepgroup')->getLoops($stepgroupLabel);
		}*/
		return $loop;
	}
}