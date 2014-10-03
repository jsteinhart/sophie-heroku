<?php
class Symbic_Form_Loader_Decorator extends Symbic_Loader_AliasMap
{
	protected $_filter = 'ucfirst';

	protected $_map = array(
		'ViewScript'					=> 'Zend_Form_Decorator_ViewScript',
		'ViewHelper'					=> 'Zend_Form_Decorator_ViewHelper',
		'Tooltip'						=> 'Zend_Form_Decorator_Tooltip',
		'PrepareElements'				=> 'Zend_Form_Decorator_PrepareElements',
		'Label'							=> 'Zend_Form_Decorator_Label',
		'Image'							=> 'Zend_Form_Decorator_Image',
		'HtmlTag'						=> 'Zend_Form_Decorator_HtmlTag',
		'FormErrors'					=> 'Zend_Form_Decorator_FormErrors',
		'FormElements'					=> 'Zend_Form_Decorator_FormElements',
		'Form'							=> 'Zend_Form_Decorator_Form',
		'File'							=> 'Zend_Form_Decorator_File',
		'Fieldset'						=> 'Zend_Form_Decorator_Fieldset',
		'Errors'						=> 'Zend_Form_Decorator_Errors',
		'DtDdWrapper'					=> 'Zend_Form_Decorator_DtDdWrapper',
		'Description'					=> 'Zend_Form_Decorator_Description',
		'Captcha'						=> 'Zend_Form_Decorator_Captcha',
		'Callback'						=> 'Zend_Form_Decorator_Callback',
		'ValidationAttributes'			=> 'Symbic_Form_Decorator_ValidationAttributes',
		'TranslateAttributes'			=> 'Symbic_Form_Decorator_TranslateAttributes',
		'TabContainer'					=> 'Symbic_Form_Decorator_TabContainer',
		'RenderValueViewHelper'			=> 'Symbic_Form_Decorator_RenderValueViewHelper',
		'NoneEmptyLabel'				=> 'Symbic_Form_Decorator_NoneEmptyLabel',
		'LegendHeadline'				=> 'Symbic_Form_Decorator_LegendHeadline',
		'InputGroup'					=> 'Symbic_Form_Decorator_InputGroup',
		'HtmlTagId'						=> 'Symbic_Form_Decorator_HtmlTagId',
		'HeaderRow'						=> 'Symbic_Form_Decorator_HeaderRow',
		'FormContentPane'				=> 'Symbic_Form_Decorator_FormContentPane',
		'ErrorAwareLabel'				=> 'Symbic_Form_Decorator_ErrorAwareLabel',
		'ErrorAwareHtmlTag'				=> 'Symbic_Form_Decorator_ErrorAwareHtmlTag',
		'ElementTypeConditional'		=> 'Symbic_Form_Decorator_ElementTypeConditional',
		'ElementAttributes'				=> 'Symbic_Form_Decorator_ElementAttributes',
		'ContentPane'					=> 'Symbic_Form_Decorator_ContentPane',
		'AttributedViewHelper'			=> 'Symbic_Form_Decorator_AttributedViewHelper',
	);

	public function activateDojoForms()
	{
		$elements = array(
			'FormContentPane'					=> 'Symbic_Dojo_Form_Decorator_FormContentPane',
			'SymbicDijitElement'				=> 'Symbic_Dojo_Form_Decorator_SymbicDijitElement',
			'SymbicDijitForm'					=> 'Symbic_Dojo_Form_Decorator_SymbicDijitForm',

			'AccordionContainer'				=> 'Zend_Dojo_Form_Decorator_AccordionContainer',
			'TabContainer'						=> 'Zend_Dojo_Form_Decorator_TabContainer',
			'AccordionPane'						=> 'Zend_Dojo_Form_Decorator_AccordionPane',
			'BorderContainer'					=> 'Zend_Dojo_Form_Decorator_BorderContainer',
			'ContentPane'						=> 'Zend_Dojo_Form_Decorator_ContentPane',
			'DijitElement'						=> 'Zend_Dojo_Form_Decorator_DijitElement',
			'DijitForm'							=> 'Zend_Dojo_Form_Decorator_DijitForm',
			'SplitContainer'					=> 'Zend_Dojo_Form_Decorator_SplitContainer',
			'StackContainer'					=> 'Zend_Dojo_Form_Decorator_StackContainer',
		);

		foreach ($elements as $name => $className)
		{
			$this->setMap($name, $className);
		}
	}

}