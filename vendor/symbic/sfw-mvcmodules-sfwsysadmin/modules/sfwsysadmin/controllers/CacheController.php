<?php
class Sfwsysadmin_CacheController extends Symbic_Controller_Action
{
	public function indexAction()
	{
		if (!$this->getModule()->isAllowed('cache'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		// Check whether cache resource is active and fetch resource instead of registry entry
		$cache = Zend_Registry::get('Zend_Cache');
		if ($cache->getBackend() instanceof Zend_Cache_Backend_Memcached)
		{
			$cacheFiles = null;
		}
		else
		{
			$cacheFiles = $cache->getIds();
		}

		$clear = $this->_getParam('clear', '');
		if ($clear == 'y')
		{
			$cache->clean(Zend_Cache::CLEANING_MODE_ALL);

			$this->_helper->flashMessenger('System cache cleared:<br />'. sizeof($cacheFiles) . ' entries deleted.');

			$this->_helper->getHelper('Redirector')->gotoRoute(array('clear' => null));
			return;
		}

		$this->view->cacheFiles = $cacheFiles;

		$this->view->breadcrumbs = array(
				array(
					'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
					'title' => 'Administration',
					'small' => 'Home:',
					'name' => 'Administration'
				),
				array(
					'title' => 'System Cache',
					'small' => 'System Cache:',
					'name' => 'Overview'
				)
			);
	}
}