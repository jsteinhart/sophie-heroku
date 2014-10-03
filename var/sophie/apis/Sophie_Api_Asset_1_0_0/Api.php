<?php
/**
 * SoPHIE Asset API Class.
 *
 * The Asset API provides media asset related functionality within a given
 * execution context.
 */
class Sophie_Api_Asset_1_0_0_Api extends Sophie_Api_Abstract
{
	/**
	 * @var null Asset database table object
	 */
	protected $assetTable = null;

	/**
	 * Set the asset database table object.
	 *
	 * @param Sophie_Db_Treatment_Asset $assetTable
	 */
	protected function setAssetTable(Sophie_Db_Treatment_Asset $assetTable)
	{
		$this->assetTable = $assetTable;
	}

	/**
	 * Init and get the asset database table object.
	 *
	 * @return Sophie_Db_Treatment_Asset
	 */
	protected function getAssetTable()
	{
		if (is_null($this->assetTable))
		{
			$this->assetTable = Sophie_Db_Treatment_Asset::getInstance();
		}
		return $this->assetTable;
	}

	/**
	 * Helper function to render HTML attributes
	 *
	 * @return String
	 */
	protected function _htmlAttribs($attribs)
	{
		$html = '';

		// escaping derived from Zend_View_Helper_HtmlElement->_htmlAttribs
		if (is_array($attribs))
		{
			foreach ($attribs as $key => $val)
			{
				// escaping derived from Zend_View->escape
				$key = htmlspecialchars($key, ENT_COMPAT, 'UTF-8');

				if (('on' == substr($key, 0, 2)) || ('constraints' == $key))
				{
					// Don't escape event attributes; _do_ substitute double quotes with singles
					if (!is_scalar($val))
					{
						// non-scalar data should be cast to JSON first
						$val = Zend_Json::encode($val);
					}
					// Escape single quotes inside event attribute values.
					// This will create html, where the attribute value has
					// single quotes around it, and escaped single quotes or
					// non-escaped double quotes inside of it
					$val = str_replace('\'', '&#39;', $val);
				}
				else
				{
					if (is_array($val))
					{
						$val = implode(' ', $val);
					}
					$val = htmlspecialchars($val, ENT_COMPAT, 'UTF-8');
				}

				if ('id' == $key)
				{
					if (strstr($val, '['))
					{
						if ('[]' == substr($val, -2))
						{
							$val = substr($val, 0, strlen($val) - 2);
						}
						$val = trim($val, ']');
						$val = str_replace('][', '-', $val);
						$val = str_replace('[', '-', $val);
					}
				}

				if (strpos($val, '"') !== false)
				{
					$html .= " $key='$val'";
				}
				else
				{
					$html .= " $key=\"$val\"";
				}

			}
		}
		elseif (is_string($attribs))
		{
			$html .= ' ' . $attribs;
		}
		
		return $html;
	}

	/**
	 * Returns the raw asset data.
	 *
	 * @param String $label
	 * @return Array
	 */
	public function get($label)
	{
		$assetTable = $this->getAssetTable();
		$asset = $assetTable->getAssetsByTreatmentIdAndLabel($this->getContext()->getTreatmentId(), $label);

		if (is_null($asset) || $asset === false) {
			return null;
		}

		return $asset->toArray();
	}

	/**
	 * Returns a base64 encoded asset to be used as inline data.
	 *
	 * @param String $label
	 * @return String
	 */
	public function inlineData($label)
	{
		$assetTable = $this->getAssetTable();
		$asset = $assetTable->getAssetsByTreatmentIdAndLabel($this->getContext()->getTreatmentId(), $label);

		if (is_null($asset) || $asset === false) {
			return '';
		}

		if (substr($asset->contentType, 0, 6) != 'image/' && substr($asset->contentType, 0, 6) != 'audio/' && substr($asset->contentType, 0, 6) != 'video/')
		{
			return '';
		}

		$assetHeader = 'data:' . $asset->contentType . ';base64,';

		return $assetHeader . base64_encode($asset->data);
	}

	/**
	 * Returns the url for an asset identified by the label.
	 *
	 * @param String $label
	 * @return String
	 */
	public function url($label)
	{
		return '/expfront/asset/index/label/' . urlencode($label);
	}

	/**
	 * Helper function to render an IMG tag
	 *
	 * @return String
	 */
	protected function _imgTag($src, $attribs = null)
	{
		return '<img src="' . $src . '"' . $this->_htmlAttribs($attribs) . ' />';
	}

	/**
	 * Returns an HTML IMG tag with inline asset data.
	 * 
	 * Returns an HTML IMG tag including the asset referenced by $label as inline data. $attribs is an associative array which is interpreted as additional attributes for the HTML IMG tag.
	 *
	 * @param String $label
	 * @param Array $attribs
	 * @return String
	 */
	public function inlineImg($label, $attribs = null)
	{
		return $this->_imgTag($this->inlineData($label), $attribs);
	}
	
	/**
	 * Returns an HTML IMG tag with the linked asset.
	 * 
	 * Returns an HTML IMG tag including the asset referenced by $label as the src link. $attribs is an associative array which is interpreted as additional attributes for the HTML IMG tag.
	 *
	 * @param String $label
	 * @param Array $attribs
	 * @return String
	 */
	public function img($label, $attribs = null)
	{
		return $this->_imgTag($this->url($label), $attribs);
	}

	/**
	 * Helper function to render an AUDIO tag
	 *
	 * @return String
	 */
	protected function _audioTag($srcs, $attribs = null)
	{
		$html = '<audio ' . $this->_htmlAttribs($attribs) . '>';
		foreach ($srcs as $src)
		{
			if (is_array($src))
			{
				$srcAttribs = $src;
				unset($srcAttribs['src']);
				$src = $src['src'];
			}
			$html = '<source src="' . $src . '"' . $this->_htmlAttribs($srcAttribs) . '>';
		}
		$html .= '</audio>';
	}

	/**
	 * Returns an HTML AUDIO tag with the linked asset or list of assets.
	 * 
	 * Returns an HTML AUDIO tag including the asset(s) referenced by $label as the sources link. $attribs is an associative array which is interpreted as additional attributes for the HTML IMG tag.
	 *
	 * @hidden 1
	 * @param String $label
	 * @param Array $attribs
	 * @return String
	 */
	public function audio($label, $attribs = null)
	{
		if (!is_array($label))
		{
			$label = array($label);
		}
		
		$assetTable = $this->getAssetTable();
		$srcs = array();
		foreach ($label as $labelLine)
		{
			$asset = $assetTable->getAssetsByTreatmentIdAndLabel($this->getContext()->getTreatmentId(), $label);
			if (is_null($asset) || $asset === false) {
				continue;
			}

			$srcs[] = array('src' => $this->url($label), 'type' => $asset->contentType);
		}
		return $this->_audioTag($srcs, $attribs);
	}

	/**
	 * Returns an HTML AUDIO tag with inline assets.
	 * 
	 * Returns an HTML AUDIO tag including the asset(s) referenced by $label as inline sources. $attribs is an associative array which is interpreted as additional attributes for the HTML IMG tag.
	 *
	 * @hidden 1
	 * @param String $label
	 * @param Array $attribs
	 * @return String
	 */
	public function inlineAudio($label, $attribs = null)
	{
		if (!is_array($label))
		{
			$label = array($label);
		}

		$assetTable = $this->getAssetTable();
		$srcs = array();
		foreach ($label as $labelLine)
		{
			$asset = $assetTable->getAssetsByTreatmentIdAndLabel($this->getContext()->getTreatmentId(), $label);
			if (is_null($asset) || $asset === false) {
				continue;
			}

			$srcs[] = array('src' => $this->inlineData($label), 'type' => $asset->contentType);
		}
		return $this->_audioTag($srcs, $attribs);
	}
}