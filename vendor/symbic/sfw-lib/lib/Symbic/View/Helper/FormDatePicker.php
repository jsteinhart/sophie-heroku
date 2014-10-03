<?php
/**
  * requires Jquery and Jquery UI
  *     and
  * a valid JqueryUI.css file!
  *
  * Example include :
  *     $this->headLink()->appendStylesheet($this->baseUrl().'/_scripts/jquery/css/blitzer/jquery-ui-1.10.3.custom.css');
  *     $this->headScript()->prependFile($this->baseUrl().'/_scripts/jquery/js/jquery-ui-1.10.3.custom.min.js')
  *                  ->prependFile($this->baseUrl().'/_scripts/jquery/js/jquery-1.9.1.js');
  * (Be carefull if you define Jquery in your applicatioin/layout you have to PREPEND the js files!)
  */

class Symbic_View_Helper_FormDatePicker extends Zend_View_Helper_FormElement
{

	public function formDatePicker($name, $value = null, $attribs = null)
	{

		$info = $this->_getInfo($name, $value, $attribs);
		extract($info); // name, id, value, attribs, options, listsep, disable

		if(is_null($value))
		{
			$now = new Zend_Date();
			$day = $now->toValue(Zend_Date::DAY);
			$month = $now->toValue(Zend_Date::MONTH);
			$year = $now->toValue(Zend_Date::YEAR);
			$value = $year.'-'.$month.'-'.$day;
		}
		else
		{
			if ($value instanceof Zend_Date)
			{
				$given = $value;
			}
			else
			{
				$given = new Zend_Date($value,'yyyy-MM-dd');
			}
			$day = $given->toValue(Zend_Date::DAY);
			$month = $given->toValue(Zend_Date::MONTH);
			$year = $given->toValue(Zend_Date::YEAR);
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

		//Init field names and attributes
		$escId = $this->view->escape($id);
		$hiddenName = $name.'_hidden';
		$hiddenAttribs = $attribs;
		$hiddenAttribs['id'] = $name.'_hidden';
		$hiddenAttribs['disabled'] = true;
		$dayName = $name.'[day]';
		$dayAttribs = $attribs;
		$dayAttribs['size'] = 2;
		$dayAttribs['id'] = $name . '_day';
		$monthName = $name.'[month]';
		$monthAttribs = $attribs;
		$monthAttribs['id'] = $name . '_month';
		$yearName = $name.'[year]';
		$yearAttribs = $attribs;
		$yearAttribs['id'] = $name.'_year';
		$yearAttribs['size'] = 4;


		$this->view->headLink()->appendStylesheet($this->view->baseUrl().'/_scripts/jquery/css/blitzer/jquery-ui-1.10.3.custom.css');
		$this->view->jQuery();
		$this->view->jQuery('jquery-ui-1.10.3.custom.min.js');

		$onLoadFunction = '
					$j = jQuery;
					$j(function(){
						/**
						 * Init Datepicker on hidden date field
						 */
						$j("#'.$hiddenName.'").datepicker({
							showOn: "button",								//show as button Image only
							buttonImage: "/_media/Icons/calendar.png",
							buttonImageOnly: true,
							dateFormat: "yy-mm-dd",
							onSelect: function (dateText, inst) { 			//Update input fields
								var pieces = dateText.split("-");
								$j("#'.$dayAttribs['id'].'").val(pieces[2]);
								$j("#'.$monthAttribs['id'].'").val(parseInt(pieces[1]));
								$j("#'.$yearAttribs['id'].'").val(pieces[0]);
							}
						});

						/**
						 * Update Datepicker if input fields get changed
						 */
						$j("#'.$dayAttribs['id'].' ,#'.$monthAttribs['id'].', #'.$yearAttribs['id'].'").change(function(){
							var d = $("#'.$yearAttribs['id'].'").val()+"-"+$("#'.$monthAttribs['id'].'").val()+"-"+$("#'.$dayAttribs['id'].'").val();
							$j("#'.$hiddenName.'").datepicker("setDate", d);
						});
					});
						';
		if(!isset($attribs['disabled']))
		{
			$this->view->headScript()->appendScript($onLoadFunction, $type = 'text/javascript');
		}

		$xhtml  = '';
		$xhtml .= $this->view->formText($dayName, $day, $dayAttribs);
		$xhtml .= '.';
		$xhtml .= $this->view->formSelect($monthName, $month, $monthAttribs, $options['multiOptions']);
		$xhtml .= ' ';
		$xhtml .= $this->view->formText($yearName, $year, $yearAttribs);
		$xhtml .= $this->view->formHidden($hiddenName, $value, $hiddenAttribs);

		return $xhtml;
	}
}