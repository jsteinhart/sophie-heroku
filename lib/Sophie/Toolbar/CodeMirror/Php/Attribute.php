<?php
class Sophie_Toolbar_CodeMirror_Php_Attribute extends Sophie_Toolbar_CodeMirror_Php
{
	private $attributeConfig = null;
	
	public function __construct(Array $attributeConfig = array())
	{
		$this->attributeConfig = $attributeConfig;
		return parent :: __construct();
	}
	
	public function getOptions($codeMirrorJsObjectName)
	{
		$items = array();
		
		foreach ($this->attributeConfig as $attrName => $attrCfg)
		{
			if (!isset($attrCfg['title']) || (isset($attrConfig[$attrName]['setByApi']) && $attrConfig[$attrName]['setByApi'] == false))
			{
				continue;
			}
			$itm = array(
				'htmlTitle' => $attrCfg['title'],
				'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'setRuntimeAttribute', '" . $attrName . "')"
			);
			if (isset($attrCfg['group']))
			{
				if (!isset($items[ $attrCfg['group'] ]))
				{
					$items[ $attrCfg['group'] ] = array(
						'title' => $attrCfg['group'],
						'items' => array()
					);
				}
				$items[ $attrCfg['group'] ]['items'][] = $itm;
			}
			else
			{
				$items[] = $itm;
			}
		}
		
		$options = array(
			array (
				'title' => 'Set Runtime Attribute',
				'htmlTitle' => '<tt>Set Runtime Attribute</tt>',
				'items' => $items
			)
		);
		
		return array_merge($options, parent :: getOptions($codeMirrorJsObjectName));
	}
}