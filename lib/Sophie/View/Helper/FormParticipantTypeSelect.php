<?php
class Sophie_View_Helper_FormParticipantTypeSelect extends Zend_View_Helper_FormElement
{
	private $dummyId = '';
	
	public function formParticipantTypeSelect($name, $value = null, $attribs = null, $options = null, $listsep = "<br />\n")
    {
		$info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
		extract($info); // name, id, value, attribs, options, listsep, disable
		
		$selected = array_map('strval', (array) $value);
		
		if (substr($name, -2) != '[]')
		{
			$name .= '[]';
		}
		$id = $this->view->escape($id);
		
		$selectAll = (count($selected) == 0);

		$allValue = '__all_' . md5(print_r($options, true));
		$ids = array(
			'allId' => 'chkbox_' . md5($name . '_' . $allValue),
			'options' => array(),
		);
		foreach ($options as $optionValue => $optionTitle)
		{
			$ids['options'][] = 'chkbox_' . md5($name . '_' . $optionValue);
		}
		$idsJson = str_replace('"', "'", json_encode($ids));
		$options = array_merge(array($allValue => 'All'), $options);

		$xhtml  = '';
		$xhtml .= '<div class="participantTypeSelect" id="' . $id . '">';
		
		foreach ($options as $optionValue => $optionTitle)
		{
			$chkboxId = 'chkbox_' . md5($name . '_' . $optionValue);
			
			$xhtml .= '<label for="' . $chkboxId . '"';
			if ($optionValue == $allValue)
			{
				$xhtml .= ' class="all"';
			}
			$xhtml .= '>';
			$xhtml .= '<input type="checkbox" name="' . $this->view->escape($name) . '" value="' . $this->view->escape($optionValue) . '" id="' . $chkboxId . '" ';
			if ($selectAll || in_array((string) $optionValue, $selected))
			{
				$xhtml .= ' checked="checked"';
			}
			$xhtml .= ' onclick="sophieParticipantTypeSelectUpdate(\'' . $chkboxId . '\', ' . $idsJson . ')" /> ';
			$xhtml .= $this->view->escape($optionTitle);
			$xhtml .= '</label>';
		}
		$xhtml .= '</div>';

		return $xhtml;
	}
}