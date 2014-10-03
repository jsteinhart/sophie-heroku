<?php
/*


	This Dummy is out of order.
	Its function do not match the necessary functions any more.


class Sophie_Steptype_Dummy extends Sophie_Steptype_Abstract
{
	public $participant;
	public $session;

	public $__categoryName = '';
	public $__printableName = 'Dummy';

	public function renderForm()
	{
		$view = $this->getView();

		$content = '<form action="' . $view->url(array (
			'action' => 'index'
		));
		$content .= '" method="POST" name="stepaction">';
		$content .= $view->formHidden('contextChecksum', $this->getContext()->getChecksum());

		// new div for old layout
		$content .= '<div id="caction"><div id="cactionhead"></div><span id="cactionform">';
		$content .= '<input name="NextStep" type="submit" value="Weiter...">';
		$content .= '</span></div></div>';
		$content .= '</form>';
		return $content;
	}

	public function render()
	{
		$content = $this->renderForm();
		return $content;
	}

	///////////////////////////////////////////////////////////////////

	public function getAdminTabs()
	{
		$myTabs = array (
			'Data'
		);
		$parentTabs = parent :: getAdminTabs();
		$tabs = array_merge($myTabs, $parentTabs);
		return $tabs;
	}

	public function renderAdminTabData()
	{
		$view = $this->getView();

		$content = '<table width="80%" border="1">';
		$content .= '<tr><th>Field</th><th>Value</th></tr>';
		$attributes = $this->getAttributeValues();
		foreach($attributes as $attributeKey => $attributeValue)
		{
			$content .= '<tr><td>' . $view->escape($attributeKey) . '</td><td>' . $view->escape($attributeValue) . '</td></tr>';
		}
		$content .= '</table>';

		return $content;
	}

	public function validateAdminTabContent()
	{
		$valid = true;
		return $valid;
	}

	public function saveAdminTabContent()
	{
	}

}
*/