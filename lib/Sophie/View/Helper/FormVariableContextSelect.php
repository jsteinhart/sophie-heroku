<?php
class Sophie_View_Helper_FormVariableContextSelect extends Zend_View_Helper_FormElement
{
	private $dummyId = '';
	
	public function formVariableContextSelect($name, $value = null, $attribs = null, $options = null, $listsep = "<br />\n")
    {
		$info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
		extract($info); // name, id, value, attribs, options, listsep, disable
		
		$selected = strval($value);
		
		$nameEsc = $this->view->escape($name);

		$table = array(
			'titleRow' => array(
				array('type' => 'th'),
				array('type' => 'th', 'value' => 'Everywhere'),
				array('type' => 'th', 'value' => 'Stepgroup'),
				array('type' => 'th', 'value' => 'Stepgroup Loop'),
			),
			'everyoneRow' => array(
				array('type' => 'th', 'value' => 'Everyone'),
				array('type' => 'td', 'value' => 'EE', 'title' => 'Everyone / Everywhere'),
				array('type' => 'td', 'value' => 'ES', 'title' => 'Everyone / Stepgroup'),
				array('type' => 'td', 'value' => 'ESL', 'title' => 'Everyone / Stepgroup Loop'),
			),
			'groupRow' => array(
				array('type' => 'th', 'value' => 'Group'),
				array('type' => 'td', 'value' => 'GE', 'title' => 'Group / Everywhere'),
				array('type' => 'td', 'value' => 'GS', 'title' => 'Group / Stepgroup'),
				array('type' => 'td', 'value' => 'GSL', 'title' => 'Group / Stepgroup Loop'),
			),
			'participantRow' => array(
				array('type' => 'th', 'value' => 'Participant'),
				array('type' => 'td', 'value' => 'PE', 'title' => 'Participant / Everywhere'),
				array('type' => 'td', 'value' => 'PS', 'title' => 'Participant / Stepgroup'),
				array('type' => 'td', 'value' => 'PSL', 'title' => 'Participant / Stepgroup Loop'),
			),
		);
		
		$xhtml  = '';
		$xhtml .= '<table class="variableContextSelect">';
		foreach ($table as $row)
		{
			$xhtml .= '<tr>';
			foreach ($row as $cell)
			{
				if (!isset($cell['value']))
				{
					$cell['value'] = '';
				}
				$valueEsc = $this->view->escape($cell['value']);
				if ($cell['type'] == 'th')
				{
					$xhtml .= '<th>' . $valueEsc . '</th>';
				}
				else
				{
					$cellId = 'radio_' . $nameEsc . '_' . $valueEsc;
					$xhtml .= '<td>';
					$xhtml .= '<label for="' . $cellId . '" title="' . $this->view->escape($cell['title']) . '">';
					$xhtml .= '<input type="radio" name="' . $nameEsc . '" value="' . $valueEsc . '" id="' . $cellId . '"';
					if ($selected == $cell['value'])
					{
						$xhtml .= ' checked="checked" ';
					}
					if (!empty($attribs['onchange']))
					{
						$xhtml .= ' onchange="' . $attribs['onchange'] . '"';
					}
					$xhtml .= '> ';
					$xhtml .= $valueEsc;
					$xhtml .= '</label>';
					$xhtml .= '</td>';
				}
			}
			$xhtml .= '</tr>';
		}
		$xhtml .= '</table>';
		return $xhtml;
	}
}