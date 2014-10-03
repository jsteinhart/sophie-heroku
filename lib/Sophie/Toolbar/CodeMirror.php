<?php
class Sophie_Toolbar_CodeMirror extends Symbic_Toolbar_CodeMirror
{
	public $options = array();
	protected $steptype = null;
	
	public function __construct($steptype = null)
	{
		if ($steptype instanceof Sophie_Steptype_Abstract)
		{
			$this->steptype = $steptype;
		}
		//else die(gettype($steptype));
	}

	public function render($view, $codeMirrorJsObjectName, $options = null)
	{
		if (!is_null($options))
		{
			$this->options = $options;
		}

		return parent :: render($view, $codeMirrorJsObjectName, $this->options);
	}
}