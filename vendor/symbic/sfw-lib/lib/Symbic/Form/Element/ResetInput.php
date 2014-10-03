<?php
class Symbic_Form_Element_ResetInput extends Symbic_Form_Element_AbstractInput
{
	public		$type			= 'reset';
	protected	$_ignore		= true;
	protected	$_required		= false;

    public function isChecked()
    {
        $value = $this->getValue();

        if (empty($value))
		{
            return false;
        }
        if ($value != $this->getLabel())
		{
            return false;
        }

        return true;
    }

	public function getRenderValue()
	{
		return $this->getLabel();
	}
}