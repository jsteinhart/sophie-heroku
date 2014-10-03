<?php
class Symbic_Form_Decorator_AttributedViewHelper extends Symbic_Form_Decorator_RenderValueViewHelper
{
	public function getElementAttribs()
	{
		return array_merge(
			(array) $this->getOption('elementDefaultAttributes'),
			(array) parent::getElementAttribs(),
			(array) $this->getOption('elementAttributes')
		);
	}
}