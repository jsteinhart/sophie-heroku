<?php
class Symbic_Mail_Queue_Task extends Symbic_Task_Abstract
{
	private $startMicrotime = 0;

	public function run(array $parameters)
	{
		$this->startMicrotime = microtime(true);

		$queue = new Symbic_Mail_Queue();
		$processedMails = $queue->sendQueue();

		echo date('Y-m-d H:i:s ');
		echo $processedMails . ' mails processed in ' . number_format(microtime(true) - $this->startMicrotime, 3) . ' s'. PHP_EOL;
		if ($queue->failedMails)
		{
			echo $queue->failedMails . ' mails failed!' . PHP_EOL;
		}
		
		return true;
	}

}
