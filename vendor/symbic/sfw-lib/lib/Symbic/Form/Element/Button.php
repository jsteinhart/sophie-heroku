<?php
class Symbic_Form_Element_Button extends Symbic_Form_Element_Checkbox
{
    protected	$_ignore			= true;
	protected	$_required			= false;
    protected	$_checkedValue 		= 'Send';
    protected	$_uncheckedValue 	= '';
    protected	$_value 			= '';	

	public		$type				= 'button';
	public		$helper				= 'formButton';
	public		$options = array(
        			'checkedValue'   => 'Send',
        			'uncheckedValue' => '',
    );
}