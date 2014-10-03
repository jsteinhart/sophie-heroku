<?php
class Sfwsysadmin_IndexController extends Symbic_Controller_Action
{
	public function indexAction()
	{
		if (!$this->getModule()->isAllowed('dashboard'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$activeComponents = $this->getModule()->getActiveComponents();

		$categories = array();
		foreach ($activeComponents as $component)
		{
			if (!isset($component['category']))
			{
				if (!in_array('Uncategorized', $categories))
				{
					$categories[] = 'Uncategorized';
				}
				continue;
			}
			if (!in_array($component['category'], $categories))
			{
				$categories[] = $component['category'];
			}
		}

		if (sizeof($categories) > 0)
		{
			sort($categories);
			$this->view->categories = $categories;
		}

		$this->view->components = $activeComponents;

		$this->view->breadcrumbs = array(
				array(
					'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
					'title' => 'Administration',
					'small' => 'Home:',
					'name' => 'Administration'
				)
			);
	}
}