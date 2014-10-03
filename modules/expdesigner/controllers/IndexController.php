<?php
class Expdesigner_IndexController extends Symbic_Controller_Action
{
	public function indexAction()
	{
		$this->_forward('index', 'experiment');
	}

}