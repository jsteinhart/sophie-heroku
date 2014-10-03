<?php
class Symbic_Form_Decorator_TranslateAttributes extends Zend_Form_Decorator_Abstract
{
	public function render($content)
	{
		$element = $this->getElement();
		if (null === ($translator = $element->getTranslator())) {
			return $content;
		}
		
		$options = $this->getOptions();
		foreach ($options as $option)
		{
			$attributeValue = $this->getElement()->getAttrib($option);
			if ($attributeValue !== null)
			{
				$this->getElement()->setAttrib($option, $translator->translate($attributeValue));
			}
		}
		return $content;
	}
}