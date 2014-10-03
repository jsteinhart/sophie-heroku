<?php
class Symbic_View_Helper_InlineScript extends Symbic_View_Helper_HeadScript
{
	public function getContainer()
	{
		return Symbic_View_Helper_Container_InlineScript::getInstance();
	}

    public function inlineScript($mode = Zend_View_Helper_HeadScript::FILE, $spec = null, $placement = 'APPEND', array $attrs = array(), $type = null)
    {
		if ($spec === null)
		{
			return $this;
		}
        return parent::headScript($mode, $spec, $placement, $attrs, $type);
    }
}