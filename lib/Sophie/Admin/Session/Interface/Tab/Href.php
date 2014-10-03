<?php
class Sophie_Admin_Session_Interface_Tab_Href extends Sophie_Admin_Session_Interface_Tab_Abstract{

	private $content = '';

	public function init()
	{
		$this->setParams(
			array(
				'preload' => 'false',
				'refreshOnShow' => 'true',
				'preventCache' => 'true'
				)
			);
	}
	
	public function setContent($content)
	{
		$this->content = $content;
	}
	
	public function getContent()
	{
		return $this->content;
	}

}