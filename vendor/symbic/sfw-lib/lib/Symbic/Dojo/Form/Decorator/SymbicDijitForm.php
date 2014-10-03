<?php
class Symbic_Dojo_Form_Decorator_SymbicDijitForm extends Zend_Dojo_Form_Decorator_DijitForm
{

    public function render($content)
    {
    	$form = $this->getElement();
    	$formId = $form->getId();

		$onSubmitScript = '';
		$onSubmitScript .= '<script type="dojo/method" event="onSubmit">' . "\n";
		$onSubmitScript .= '<!--' . "\n";
		$onSubmitScript .= 'if (!this.validate())' . "\n";
		$onSubmitScript .= '{' . "\n";

		if (sizeof($form->getSubForms())>0)
		{
			$onSubmitScript .= '	validateTabbedForm(\'' . $formId . '\');' . "\n";
		}

		$onSubmitScript .= '	return false;' . "\n";
		$onSubmitScript .= '}' . "\n";
		$onSubmitScript .= 'return true;' . "\n";
		$onSubmitScript .= '-->' . "\n";
		$onSubmitScript .= '</script>' . "\n";

        return parent::render($onSubmitScript . $content);
    }
}