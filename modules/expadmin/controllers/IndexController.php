<?php
class Expadmin_IndexController extends Symbic_Controller_Action
{
	public function init()
	{
	}

	public function indexAction()
	{
		$this->_forward('index', 'session', 'expadmin');
	}

}