<?php
class Symbic_View_Helper_FormCheckboxInlineLabel extends Zend_View_Helper_FormCheckbox
{
    public function formCheckboxInlineLabel($name, $value = null, $attribs = null, array $checkedOptions = null)
    {
		if ($attribs === null || !is_array($attribs) || !isset($attribs['inlineLabel']))
		{
			throw new Exception('inlineLabel option required for checkboxInlineLabel element');
		}
		
		$inlineLabelOptions = array(
			'escape' => false,
			'disableFor' => false
		);
		if (isset($attribs['inlineLabelOptions']))
		{
			$inlineLabelOptions = array_merge($inlineLabelOptions, $attribs['inlineLabelOptions']);
			unset($attribs['inlineLabelOptions']);
		}

		$inlineLabel = $attribs['inlineLabel'];
		unset($attribs['inlineLabel']);
		if (null !== $translator = $this->getTranslator())
		{
			$inlineLabel = $translator->translate($inlineLabel);
		}
		
		if (isset($attribs['inlineLabelPlacement']))
		{
			$inlineLabelPlacement = (string)$attribs['inlineLabelPlacement'];
			unset($attribs['inlineLabelPlacement']);
		}
		else
		{
			$inlineLabelPlacement = 'append';
		}

		$content = parent::formCheckbox($name, $value, $attribs, $checkedOptions);
		if ($inlineLabelPlacement === 'prepend')
		{
			$content = $inlineLabel . ' ' . $content;
		}
		else
		{
			$content = $content . ' ' . $inlineLabel;
		}
	
		return $this->view->formLabel($name, $content, $inlineLabelOptions);
    }
}