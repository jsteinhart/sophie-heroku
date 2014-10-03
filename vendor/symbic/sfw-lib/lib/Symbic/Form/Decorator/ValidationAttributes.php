<?php
class Symbic_Form_Decorator_ValidationAttributes extends Zend_Form_Decorator_Abstract
{
	public function render($content)
	{
		$element = $this->getElement();
		
		$attribs = array();

		$validators = $element->getValidators();
		
		// TODO: chek whether noneempty is active
		if ($element->isRequired())
		{
			//$element->setAttrib('required', true);
		}
	
		return $content;

/*		if ($element instanceof Symbic_Form_Element_Number)
		{
			foreach ($validators as $validator)
			{
				// Between
				// LessThan
				// GreaterThan
			}
		}
*/

		// TODO: elseif ($element instanceof Symbic_Form_Element_Date)
		// TODO: elseif ($element instanceof Symbic_Form_Element_Datetime)
		// TODO: elseif ($element instanceof Symbic_Form_Element_Datetime_Local)
		// Symbic_Form_Element_Month
		// Symbic_Form_Element_Time
		// Symbic_Form_Element_Week

		/*
		// we do not need to do anything here
		elseif ($element instanceof Color)
		{
		}
		*/
		
		/*
		// we do not need to do anything here
		elseif ($element instanceof Symbic_Form_Element_Email)
		{
		}
		*/

		/*
		// we do not need to do anything here
		elseif ($element instanceof Symbic_Form_Element_Tel)
		{
		}
		*/
		
		/*
		elseif ($element instanceof Symbic_Form_Element_File)
		{
			// TODO: add accept attribute
		}
		*/		
		// match input element validation to html form element type:

		// image ???
		// password ???
		// radio
		// range

		//	search


		//	url
		//	week
		//  list


/*
		if (method_exists($element, 'getHtmlType'))
		{
			$htmlType = $element->getHtmlType();
		}

		foreach ($validators as $validator)
		{
			// digity
			// type="text" pattern="\d*"
			
			// alpha, alnum
			// date -> date

			//barcode, ccnum, CreditCard -> text
			// greaterThan -> min
			// hex -> text
			// hostname -> text
			// iban -> text
			// identical ??
			// in_array -> list
			// ip -> ??
			// isbn ->
			// LessThan -> max
			// NotEmpty -> required
			// PostCode -> text
			// regexp -> pattern
			// StringLength -> maxlength
			
			
			// between
			//
		}
		
		$htmlType = 'text';

		if ($type == maxlength
		
		$attribs['type'] = $htmlType;
		
		if (
		pattern

		if (in_array($attribs['type'], array('number', 'date', 'datetime', 'datetime-local', 'range'))
		{
			// min, max for dates and numbers
		}

		if (in_array($attribs['type'], array('number', 'date', 'datetime', 'datetime-local', 'range'))
		{
			// step for number and range
		}

		if ($attribs['type'] == 'file')
		{
			// accept for file
		}

		$this->getElement()->setAttribs($attribs);

		return $content;
	}
			// only decorate text input
		// TODO: use datalist for combobox styles and accept for file
		/*
		<datalist id="browsers">
			<option value="Internet Explorer">
			<option value="Firefox">
			<option value="Chrome">
			<option value="Opera">
			<option value="Safari">
		</datalist>
		*/
	}
}
