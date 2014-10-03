<?php
class Symbic_Form_Element_ImageInput extends Symbic_Form_Element_AbstractInput
{
	public $type 	= 'image';
    public $src;
    protected $_imageValue;

    public function setImage($path)
    {
        $this->src = (string) $path;
        return $this;
    }

    public function getImage()
    {
        return $this->src;
    }

    public function setImageValue($value)
    {
        $this->_imageValue = $value;
        return $this;
    }

    public function getImageValue()
    {
        return $this->_imageValue;
    }

    public function isChecked()
    {
        $imageValue = $this->getImageValue();
        return ((null !== $imageValue) && ($this->getValue() == $imageValue));
    }
}