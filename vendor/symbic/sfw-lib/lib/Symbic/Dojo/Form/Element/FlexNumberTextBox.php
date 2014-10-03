<?php
class Symbic_Dojo_Form_Element_FlexNumberTextBox extends Zend_Dojo_Form_Element_NumberTextBox
{
	/*
	 *  In Zend_Dojo_Form_Element_NumberTextBox $places is casted with (int).
	 *  Therefore giving a flexible number of places ('0,8') did not work.
	 *  Sometimes I **** the Zend Framework.
	 */
    public function setPlaces($places)
    {
        $this->setConstraint('places', $places);
        return $this;
    }
}