<?php

/**
 *
 */
abstract class Symbic_Task_AbstractTask
{
	protected $name;
	protected $description = '';

	protected $options;
	protected $parameters;

	final public function __construct(array $options)
	{
		$this->options = $options;

		if (empty($this->name))
		{
			$this->name = get_class($this);
		}

		if (empty($this->mutexName))
		{
			$this->mutexName = get_class($this);
		}

		$this->init();
	}

	/**
	 *	must be implemented -- will be run to perform a task
	 */
	abstract public function run(array $parameters);

	/**
	 *	can be used to implement initialization functionality
	 */
	public function init()
	{
	}

	/**
	 *	returns the task's (if given: descriptive, human readable) name
	 */
	public function getTaskName()
	{
		return $this->name;
	}

	/**
	 *	returns the task's description
	 */
	public function getTaskDescription()
	{
		return $this->description;
	}

}
