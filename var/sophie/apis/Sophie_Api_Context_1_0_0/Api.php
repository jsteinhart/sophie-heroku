<?php
/**
 * SoPHIE Context API Class
 *
 * The Context API allows access to all parameters defining the
 * context within which SoPHIE runs steps. It is used throughout
 * the steptypes and within the rendering engine and scripting
 * engine to provide a unified interface for all parts interaction
 * with the context.
 */
class Sophie_Api_Context_1_0_0_Api extends Sophie_Api_Abstract
{
	/**
	 * Return a string that is used to uniquely identify the current procedural context.
	 * 
	 * @return String
	 */
	public function getChecksum()
	{
		return $this->getContext()->getChecksum();
	}

	/**
	 * Get details for the treatment used within the current context. The function returns an array of raw database entry values.
	 *
	 * @return Array
	 */
	public function getTreatment()
	{
		return $this->getContext()->getTreatment();
	}

	/**
	 * Returns the current stepgroup label of the context.
	 *
	 * @return String
	 */
	public function getStepgroupLabel()
	{
		return $this->getContext()->getStepgroupLabel();
	}

	/**
	 * Returns the current stepgroup loop of the context.
	 *
	 * @return Integer
	 */
	public function getStepgroupLoop()
	{
		return $this->getContext()->getStepgroupLoop();
	}

	/**
	 * Returns the current step label of the context.
	 *
	 * @return String
	 */
	public function getStepLabel()
	{
		return $this->getContext()->getStepLabel();
	}

	/**
	 * Returns the current participant label of the context.
	 *
	 * @return String
	 */
	public function getParticipantLabel()
	{
		return $this->getContext()->getParticipantLabel();
	}

	/**
	 * Returns the current group label of the context.
	 *
	 * @return String
	 */
	public function getGroupLabel()
	{
		$personContextLevel = $this->getContext()->getPersonContextLevel();
		if ($personContextLevel == 'none')
		{
			throw new Exception('Person level of context is ' . $personContextLevel . '. Cannot get current Group Label.');
		}
		elseif ($personContextLevel == 'group')
		{
			return $this->getContext()->groupLabel;
		}
		else
		{
			return $this->getContext()->getApi('group')->translateLabel('%current%');
		}
	}

	/**
	 * Get details for the experiment used within the current context. The function returns an array of raw database entry values.
	 *
	 * @return Array
	 */
	public function getExperiment()
	{
		return $this->getContext()->getExperiment();
	}

	/**
	 * Get details for the stepgroup used within the current context. The function returns an array of raw database entry values.
	 *
	 * @return Array
	 */
	public function getStepgroup()
	{
		return $this->getContext()->getStepgroup();
	}

	/**
	 * Get details for the step used within the current context. The function returns an array of raw database entry values.
	 *
	 * @return Array
	 */
	public function getStep()
	{
		return $this->getContext()->getStep();
	}

	/**
	 * Get details for the session used within the current context. The function returns an array of raw database entry values.
	 *
	 * @return Array
	 */
	public function getSession()
	{
		return $this->getContext()->getSession();
	}

	/**
	 * Returns the current participant type label of the context.
	 *
	 * @return String
	 */
	public function getParticipantTypeLabel()
	{
		return $this->getContext()->getParticipantTypeLabel();
	}

	/**
	 * Get details for the participant used within the current context. The function returns an array of raw database entry values.
	 *
	 * @return Array
	 */
	public function getParticipant()
	{
		return $this->getContext()->getParticipant();
	}

	/**
	 * Determine whether the current context is called from a running session or as for a preview.
	 *
	 * @return Boolean
	 */
	public function isPreview()
	{
		return $this->getContext()->getPreviewMode();
	}
}
