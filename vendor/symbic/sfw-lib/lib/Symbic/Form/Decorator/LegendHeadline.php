<?php
class Symbic_Form_Decorator_LegendHeadline extends Zend_Form_Decorator_HtmlTag
{
    public function getLegend()
    {
        if ((null !== ($element = $this->getElement()))) {
            if (method_exists($element, 'getLegend')) {
                return $element->getLegend();
            }
        }
        return '';
    }

    public function render($content)
    {
        $legend  = $this->getLegend();

		$element = $this->getElement();
		if (null !== ($translator = $element->getTranslator())) {
			$legend = $translator->translate($legend);
		}

		if (empty($legend))
		{
			return $content;
		}

        $view    = $element->getView();
		
        $tag         = $this->getTag();
        $placement   = $this->getPlacement();
        $noAttribs   = $this->getOption('noAttribs');
        $openOnly    = $this->getOption('openOnly');
        $legendOnly  = $this->getOption('legendOnly');
        $closeOnly   = $this->getOption('closeOnly');
        $this->removeOption('noAttribs');
        $this->removeOption('openOnly');
        $this->removeOption('legendOnly');
        $this->removeOption('closeOnly');

        $attribs = null;
        if (!$noAttribs) {
            $attribs = $this->getOptions();
        }

        $id      = (string)$element->getId();
        if (!array_key_exists('id', $attribs) && '' !== $id) {
            $attribs['id'] = 'headline-' . $id;
        }

        switch ($placement) {
            case self::APPEND:
                if ($closeOnly) {
                    return $content . $this->_getCloseTag($tag);
                }
                if ($legendOnly) {
                    return $content . $view->escape($legend);
                }
                if ($openOnly) {
                    return $content . $this->_getOpenTag($tag, $attribs);
                }
                return $content
                     . $this->_getOpenTag($tag, $attribs)
					 . $view->escape($legend)
                     . $this->_getCloseTag($tag);
            case self::PREPEND:
			default:
                if ($closeOnly) {
                    return $this->_getCloseTag($tag) . $content;
                }
                if ($legendOnly) {
                    return $view->escape($legend) . $content;
                }
                if ($openOnly) {
                    return $this->_getOpenTag($tag, $attribs) . $content;
                }
                return $this->_getOpenTag($tag, $attribs)
					 . $view->escape($legend)
                     . $this->_getCloseTag($tag)
                     . $content;
        }		
    }
}