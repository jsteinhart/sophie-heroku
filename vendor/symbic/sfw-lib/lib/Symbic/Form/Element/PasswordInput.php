<?php
class Symbic_Form_Element_PasswordInput extends Symbic_Form_Element_AbstractInput
{
	public $type				= 'password';
    public $renderPassword		= false;

    public function setRenderPassword($flag)
    {
        $this->renderPassword = (bool) $flag;
        return $this;
    }

    public function renderPassword()
    {
        return $this->renderPassword;
    }

    public function isValid($value, $context = null)
    {
        foreach ($this->getValidators() as $validator) {
            if ($validator instanceof Zend_Validate_Abstract) {
                $validator->setObscureValue(true);
            }
        }
        return parent::isValid($value, $context);
    }
}