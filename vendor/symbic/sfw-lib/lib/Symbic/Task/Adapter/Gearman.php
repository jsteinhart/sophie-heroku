<?php
class Symbic_Task_Adapter_Gearman extends Symbic_Task_Adapter_Abstract
{
	protected $client;

	public function run($taskName, array $taskParameters, array $taskOptions)
	{
		$this->client = new GearmanClient();
		// default to localhost: TODO pass gearman config
		$this->client->addServer();

		# let gearman handle the task
		
		$result = $this->client->do($taskName, $taskParameters, $taskOptions);
		
		$returnCode = $this->client->returnCode();
		
		// TODO: handle $returnCode of value GEARMAN_WORK_DATA, GEARMAN_WORK_STATUS, GEARMAN_WORK_FAIL
		
		return $this->client->returnCode() == GEARMAN_SUCCESS;
	}
}