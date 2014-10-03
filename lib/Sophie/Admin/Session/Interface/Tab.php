<?php

class Sophie_Admin_Session_Interface_Tab extends Sophie_Admin_Session_Interface_Tab_Abstract{

	private $content = '';

	public function setContent($content)
	{
		$this->content = $content;
	}
	
	public function getContent()
	{
		return $this->content;
	}

}