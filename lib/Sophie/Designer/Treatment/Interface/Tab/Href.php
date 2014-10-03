<?php
class Sophie_Designer_Treatment_Interface_Tab_Href extends Sophie_Designer_Treatment_Interface_Tab_Abstract{

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