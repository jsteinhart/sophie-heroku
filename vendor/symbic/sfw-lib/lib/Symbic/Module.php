<?php

/**
 * THIS CLASS SHOULD BE ELIMINATED IN FAVOR OF Symbic_Module_Standard
 */
class Symbic_Module extends Symbic_Module_Standard
{
	protected $resourceClassNaming = 'zend1';

	/**
	 *
	 * @var type
	 */
	protected $resourceAutoloader;

	/**
	 *
	 * @return type
	 */
	protected function getResourceAutoloader()
	{
		// TODO: deactivate when resourceClassNaming == namespace?
		if ($this->resourceAutoloader === null)
		{
			$this->resourceAutoloader = new Zend_Application_Module_Autoloader(
				array(
				'basePath'	 => $this->getBasePath(),
				'namespace'	 => $this->getNamespace(),
				)
			);
			$this->resourceAutoloader->addResourceTypes(array(
				'task' => array(
					'namespace'	 => 'Task',
					'path'		 => 'tasks',
				)
			));
		}
		return $this->resourceAutoloader;
	}

	public function bootstrap()
	{
		if ($this->bootstraped)
		{
			return;
		}

		if ($this->resourceClassNaming != 'namespace')
		{
			$resourceAutoloader = $this->getResourceAutoloader();
		}
		
		parent::bootstrap();
		
		$this->bootstraped = true;
	}
}