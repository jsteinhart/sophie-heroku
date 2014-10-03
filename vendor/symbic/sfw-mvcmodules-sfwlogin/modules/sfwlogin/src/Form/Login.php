<?php
namespace Sfwlogin\Form;

class Login extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Login');

		$login = $this->createElement('TextInput', 'login');
		$login->setLabel('##loginElementLabel##');
		$login->setRequired(true);
		$login->setAttrib('placeholder', '##loginElementPlaceholder##');

		$forgotPasswordLink = $this->getView()->url(array('module'=>'sfwlogin', 'controller'=>'forgotpassword'), 'default', true);

		$password = $this->createElement('PasswordInput', 'password');
		$password->setLabel('##passwordElementLabel##');
		$password->setRequired(true);
		$password->setRenderPassword(false);
		$password->setAttrib('placeholder', '##passwordElementPlaceholder##');

		$password->setExtra('input-group-append', array(
				'content' => '<a href="%1$s">##forgotPasswordLink##</a>',
				'replace' => array($forgotPasswordLink)
			)
		);

		$rememberMe = $this->createElement('CheckboxInlineLabel', 'rememberMe');
		$rememberMe->setAttrib('inlineLabel', '##loginRememberMe##');

		$captcha = $this->createElement('captcha', 'captcha');
		$captcha->setLabel('##loginCaptchaElementLabel##');
		$captcha->setDescription('##loginCaptchaElementDescription##');
		
		$submit = $this->createElement('SubmitButton', 'submit');
		$submit->setAttrib('content', '##submitElementLabel##');
		$submit->setClass('btn btn-primary btn-block');

		$hash = $this->createElement('hash', 'csrf', array(
            'ignore' => true,
        ));

		$this->addElements(array($login, $password, $captcha, $rememberMe, $submit, $hash));
	}
}