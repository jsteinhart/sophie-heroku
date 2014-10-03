<?php

/**
 *
 */
class Symbic_Task_Test_Sleep extends Symbic_Task_AbstractTask
{

	protected $name = 'Symbic Task Test Sleep';
	protected $description = 'Sleeps 30 seconds while testing the task functionality of the Symbic Framework.';

	/**
	 *
	 */
	public function run(array $parameters = array())
	{
		echo 'TEST STARTED. GOING TO SLEEP NOW' . PHP_EOL;
		sleep(30);
		echo 'TEST SUCCESSFUL' . PHP_EOL;
	}

}
