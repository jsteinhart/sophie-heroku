<?php
class Sfwsysadmin_ModuleController extends Symbic_Controller_Action
{
	public function indexAction()
	{
		if (!$this->getModule()->isAllowed('module'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}
		
		$this->view->mvcModules = $this->getModuleManager()->getModules();

		$this->view->breadcrumbs = array(
				array(
					'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
					'title' => 'Administration',
					'small' => 'Home:',
					'name' => 'Administration'
				),
				array(
					'title' => 'Modules',
					'small' => 'Modules:',
					'name' => 'MVC'
				)
			);
	}
}