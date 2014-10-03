<?php
class Sophie_Steptype_Admin_Form extends Symbic_Form_Standard
{
	protected $_defaultDecoratorsetClass = 'Symbic_Form_Decoratorset_Tabcontainer_Table';
	
	public function init()
	{
        $this->setName('adminStepForm');
	}
}