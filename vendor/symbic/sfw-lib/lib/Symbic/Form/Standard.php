<?php
class Symbic_Form_Standard extends Symbic_Form_AbstractForm
{
	protected $_defaultSubFormClass			= 'Symbic_Form_SubForm_Standard';
	protected $_defaultDisplayGroupClass	= 'Symbic_Form_DisplayGroup_Standard';

	protected $_defaultElementLoader		= 'Symbic_Form_Loader_Element';
	protected $_defaultDecoratorLoader		= 'Symbic_Form_Loader_Decorator';
	protected $_defaultFilterLoader			= 'Symbic_Form_Loader_Filter';
	protected $_defaultValidateLoader		= 'Symbic_Form_Loader_Validate';
	
	protected $_defaultDecoratorsetClass	= 'Symbic_Form_Decoratorset_Table';
}