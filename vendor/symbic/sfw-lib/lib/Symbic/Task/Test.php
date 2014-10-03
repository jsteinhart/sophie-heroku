<?php

/**
 *
 */
class Symbic_Task_Test extends Symbic_Task_AbstractTask
{

	protected $name = 'Symbic Task Test';
	protected $description = 'Allows to test the task functionality of the Symbic Framework.';

	/**
	 *
	 */
	public function run(array $parameters = array())
	{
		echo 'TEST SUCCESSFUL' . PHP_EOL;
	}

}
