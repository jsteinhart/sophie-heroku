<?php
namespace Sfwlogin\Form\Forgotpassword;

class Login extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Reset your Password');

		$login = $this->createElement('text', 'login');
		$login->setLabel('##forgotPasswordLoginElementLabel##');
		$login->setRequired(true);

		$captcha = $this->createElement('captcha', 'captcha');
		$captcha->setLabel('##forgotPasswordCaptchaElementLabel##');
		$captcha->setDescription('##forgotPasswordCaptchaElementDescription##');
		
		$submit = $this->createElement('submit', 'submit')->setLabel('##forgotPasswordSubmitElementLabel##');

		$hash = $this->createElement('hash', 'csrf', array(
            'ignore' => true,
        ));

		$this->addElements(array($login, $captcha, $submit));
	}
}