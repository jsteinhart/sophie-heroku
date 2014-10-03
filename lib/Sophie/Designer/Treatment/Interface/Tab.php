<?php

class Sophie_Designer_Treatment_Interface_Tab extends Sophie_Designer_Treatment_Interface_Tab_Abstract{

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