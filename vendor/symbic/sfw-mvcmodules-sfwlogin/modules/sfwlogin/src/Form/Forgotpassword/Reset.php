<?php
class Sfwlogin_Form_Forgotpassword_Reset extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Set a new password');

		$this->addElement(
			'text',
			'login',
			array(
				'label' => '##loginElementLabel##',
				'required' => true,
			)
		);

		$this->addElement(
			'text',
			'token',
			array(
				'label' => 'Token',
				'required' => true,
			)
		);

		$this->addElement(
			'password',
			'password',
			array(
				'label' => 'New Password',
				'required' => true,
				'validators' => array(
					array(
						'identical',
						false,
						array(
							'token' => 'password2',
						)
					)
				)
			)
		);

		$this->addElement(
			'password',
			'password2',
			array(
				'label' => 'Repeat Password',
				'required' => true,
			)
		);

		$captcha = $this->createElement('captcha', 'captcha');
		$captcha->setLabel('##forgotPasswordResetCaptchaElementLabel##');
		$captcha->setDescription('##forgotPasswordResetCaptchaElementDescription##');
		$this->addElement($captcha);
		
		$this->addElement(
			'submit',
			'submit',
			array(
				'label' => 'Change Password',
			)
		);

		$hash = $this->createElement('hash', 'csrf', array(
            'ignore' => true,
        ));
		$this->addElement($hash);

	}
}