<?php
abstract class Symbic_Task_Adapter_Abstract
{
	protected $options;

	final public function __construct(array $adapterOptions)
	{
		$this->options = $adapterOptions;
		$this->init();
	}

	public function init()
	{
	}

	abstract public function run($taskName, array $taskParameters, array $taskOptions);
}