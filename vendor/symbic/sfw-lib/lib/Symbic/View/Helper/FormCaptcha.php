<?php
class Symbic_View_Helper_FormCaptcha extends Symbic_View_Helper_FormInput
{
	public function renderCaptcha($name, $value = null, $attribs = null)
	{
		if (isset($attribs['captcha']))
		{
			$captchaAttribs = $attribs['captcha'];
			unset($attribs['captcha']);
		}
		else
		{
			$captchaAttribs = array();
		}
		
		// handle captcha
		$captchaStr = '<img id="' . $this->getId() . '-captcha" src="' . $captchaInline . '">';
		
		if (isset($attribs['captcha']['placement']) && $attribs['captcha']['placement'] == 'prepend')
		{
			return $captchaStr . $this->renderInput($name, '', $attribs);
		}
		else
		{
			return $captchaStr . $this->renderInput($name, '', $attribs);
		}
	}
	
    public function formCaptcha($name, $value = null, $attribs = null)
    {
		return $this->renderCaptcha($name, $value, $attribs); 		
    }
}
