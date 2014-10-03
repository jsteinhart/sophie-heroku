<?php
class Symbic_Form_Decorator_HtmlTagId extends Zend_Form_Decorator_HtmlTag
{

    public function setId($id)
    {
        $this->setOption('id', $id);
        return $this;
    }

    public function getId()
    {
        $id = $this->getOption('id');
        if (null === $id) {
            if (null !== ($element = $this->getElement())) {
				$id = $this->getOption('idPrefix');
                $id .= $element->getId();
                $this->setId($id);
            }
        }

        return $id;
    }

    public function render($content)
    {
		$id = $this->getId();
        $this->removeOption('idPrefix');

		return parent::render($content);
	}
}