<?php
class Symbic_View_Helper_FormDateTimePicker extends Zend_View_Helper_FormElement
{

	public function formDateTimePicker($name, $value = null, $attribs = null)
	{

		$info = $this->_getInfo($name, $value, $attribs);
		extract($info); // name, id, value, attribs, options, listsep, disable

		if(is_null($value))
		{
			$now = new Zend_Date();
			$date = $now->toString('yyyy-MM-dd');
			$time = $now->toString('HH:mm:ss');
		}
		else
		{
			$given = new Zend_Date($value, 'yyyy-MM-dd HH:mm:ss');
			$date = $given->toString('yyyy-MM-dd');
			$time = $given->toString('HH:mm:ss');
		}

		if(isset($attribs['style']))
		{
			$attribs['style'] .= 'width:auto;';
		}
		else
		{
			$attribs['style'] = 'width:auto;';
		}
		if(!isset($options['multiOptions']))
		{
			$monthValues = array(
				1  => $this->view->T('January'),
				2  => $this->view->T('February'),
				3  => $this->view->T('March'),
				4  => $this->view->T('April'),
				5  => $this->view->T('May'),
				6  => $this->view->T('June'),
				7  => $this->view->T('July'),
				8  => $this->view->T('August'),
				9  => $this->view->T('September'),
				10 => $this->view->T('October'),
				11 => $this->view->T('November'),
				12 => $this->view->T('December')
			);
			$options['multiOptions'] = $monthValues;
		}

		$attribs['options'] = $options;

		//Init helper
		$formDatePicker = new Symbic_View_Helper_FormDatePicker();
		$formDatePicker->setView($this->view);
		$formTimePicker = new Symbic_View_Helper_FormTimePicker();
		$formTimePicker->setView($this->view);

		$xhtml  = '';
		$xhtml .= $formDatePicker->formDatePicker($name, $date, $attribs);
		$xhtml .= ' ';
		$xhtml .= $formTimePicker->formTimePicker($name, $time, $attribs);
		return $xhtml;
	}
}