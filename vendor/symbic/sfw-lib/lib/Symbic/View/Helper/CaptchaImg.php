<?php
class Symbic_View_Helper_CaptchaImg extends Zend_View_Helper_HtmlElement
{
    public function captchaImg($value, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable
		
		$captchaBuilder = new Gregwar\Captcha\CaptchaBuilder($value);
		
		if (!isset($attribs['width']))
		{
			$attribs['width'] = 150;
		}

		if (!isset($attribs['height']))
		{
			$attribs['height'] = 40;
		}
		
		if (isset($attribs['font']))
		{
			$font = $attribs['font'];
			unset($attribs['font']);
		}
		else
		{
			$font = null;
		}

		if (isset($attribs['fingerprint']))
		{
			$fingerprint = $attribs['fingerprint'];
			unset($attribs['fingerprint']);
		}
		else
		{
			$fingerprint = null;
		}

		$captchaBuilder->build($attribs['width'], $attribs['width'], $font, $fingerprint);
		
        return '<img src="' . $captchaBuilder->inline() . '"'
                . $this->_htmlAttribs($attribs)
                . $this->getClosingBracket();
    }
}
