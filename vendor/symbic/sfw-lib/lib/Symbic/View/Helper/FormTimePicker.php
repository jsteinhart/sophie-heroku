<?php
require_once 'Zend/View/Helper/FormElement.php';

/**
  * requires Jquery and Jquery UI
  * in addition it needs
  * jquery.timepicker.js in _scripts/jQuery/js/
  * and
  * jquery.timepicker.css in _scripts/jQuery/css/
  *
  * Example include :
  *     $this->headScript()->prependFile($this->baseUrl().'/_scripts/jquery/js/jquery-ui-1.10.3.custom.min.js')
  *                  ->prependFile($this->baseUrl().'/_scripts/jquery/js/jquery-1.9.1.js');
  * (Be carefull if you define Jquery in your applicatioin/layout you have to PREPEND the js files!)
  */

class Symbic_View_Helper_FormTimePicker extends Zend_View_Helper_FormElement
{

	public function formTimePicker($name, $value = null, $attribs = null)
	{

		$info = $this->_getInfo($name, $value, $attribs);
		extract($info); // name, id, value, attribs, options, listsep, disable

		if(is_null($value))
		{
			$now = new Zend_Date();
			$time = $now->toString('HH:mm');
		}
		else
		{
			$now = new Zend_Date();

			try
			{
				if(is_array($value)){
					$value = $value['time'];
				}
				$now->setTime($value);
				$time = $now->toString('HH:mm');
			}
			catch (Exception $e){
				throw new Exception($e);
			}
		}

		if(isset($attribs['style']))
		{
			$attribs['style'] .= 'width:auto;';
		}
		else
		{
			$attribs['style'] = 'width:auto;';
		}

		//Init field names and attributes
		$escId = $this->view->escape($id);

		$timeName = $name.'[time]';
		$timeAttribs = $attribs;
		$timeAttribs['size'] = 6;
		$timeAttribs['id'] = $name.'_time';

		//Init helper
		$formText = new Zend_View_Helper_FormText();
		$formText->setView($this->view);

		$this->view->jQuery();
		$onLoadFunction = '
					$j = jQuery;
					$j(function(){
						$j("#'.$timeAttribs['id'].'").timepicker({
								"timeFormat" : "H:i",
								"step": 15
						});
					});
						';
		$this->view->headScript()->appendFile($this->view->baseUrl().'/_scripts/jquery/js/jquery.timepicker.js', $type = 'text/javascript')
									->appendScript($onLoadFunction, $type = 'text/javascript');
		$this->view->headLink()->appendStylesheet($this->view->baseUrl().'/_scripts/jquery/css/jquery.timepicker.css');

		$xhtml  = '';
		$xhtml .= $formText->formText($timeName, $time, $timeAttribs);
		$xhtml .= '<img src="/_media/Icons/time.png" onClick=" $j(\'#'.$timeAttribs['id'].'\').timepicker(\'show\');">';

		return $xhtml;
	}
}