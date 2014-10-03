<?php
class Application_SfwinstallerBootstrap extends Symbic_Application_Bootstrap
{
	protected function _initMyView()
	{
		parent::_initMyView();

		$options = $this->getOptions();
		$view = $this->getResource('view');

		$view->headTitle('Installer');
	}

	protected function _initMyDb()
	{
	}
}