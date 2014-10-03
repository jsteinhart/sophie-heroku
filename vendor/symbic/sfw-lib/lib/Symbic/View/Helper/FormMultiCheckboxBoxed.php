<?php
class Symbic_View_Helper_FormMultiCheckboxBoxed extends Zend_View_Helper_FormMultiCheckbox
{
	public function formMultiCheckboxBoxed($name, $value = null, $attribs = null,
										   $options = null, $listsep = "<br />\n")
	{
		$divId = 'multi_checkbox_container_' . md5($name);
		$xhtml = '';
		$xhtml .= '<div class="symbic_form_multi_checkbox_container" id="' . $divId . '">';
		$xhtml .= parent :: formMultiCheckbox($name, $value, $attribs,
			$options, $listsep);
		$xhtml .= '</div>';

		$firstChecked = 0;
		$i = 0;
		if (is_array($value)) {
			foreach ($options as $k => $v) {
				if (in_array($k, $value)) {
					$firstChecked = $i;
					break;
				}
				$i++;
			}
			if ($firstChecked > 0) {
				$onLoadFunction = 'function() {
					dojo.byId("' . $divId . '").scrollTop = ' . ($firstChecked * 20) . ';
				}';
				$this->view->dojo()->addOnLoad($onLoadFunction);
			}
		}
		return $xhtml;
	}
}