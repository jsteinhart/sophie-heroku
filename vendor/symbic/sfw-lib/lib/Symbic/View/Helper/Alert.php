<?php
class Symbic_View_Helper_Alert extends Zend_View_Helper_Abstract
{
	public function alert( $content, $type = 'info', $escape = false)
	{
		if ($escape)
		{
			$content = $this->escape($content);
		}
		return '<div class="alert alert-' . $type . '">' . $content . '</div>';
	}
}