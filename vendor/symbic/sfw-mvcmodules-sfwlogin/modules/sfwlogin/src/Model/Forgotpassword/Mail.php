<?php
namespace Sfwlogin\Model\Forgotpassword;

class Mail extends \Symbic_Singleton
{
	public function sendChangePasswordMail($userLogin, $userName, $userEmail, $token, $validUntil, $linkReset, $config)
	{
		//print_r(array($userLogin, $userName, $userEmail, $token, $validUntil, $linkReset, $config));
		$mail = new \Zend_Mail('UTF-8');
		
		// TODO: set from config
		//$mail->setFrom($senderEmail, $senderName);
		
		$mail->addTo($userEmail, $userName);

		$messageReplacements = array(
			'userName' => $userName,
			'userLogin' => $userLogin,
			'userEmail' => $userEmail,
			'token' => $token,
			'validUntil' => $validUntil,
			'validUntilFormated' => date('D, d M Y H:i:s', $validUntil),
			'linkReset'	=> $linkReset
			// TODO: add the following:
			// 'loginUrl' => 
			// 'systemAdminName' =>
			// 'systemAdminEmail' =>
		);

		$m = new \Mustache_Engine();

		$mail->setSubject($m->render($config['mailSubject'], $messageReplacements));
		$mail->setBodyText($m->render($config['mailTemplateText'], $messageReplacements));
		$mail->setBodyHtml($m->render($config['mailTemplateHtml'], $messageReplacements));

		try
		{
			$mail->send();
		}
		catch(Exception $e)
		{
			throw new Exception('Failed to send email to ' . $userEmail);
		}
		return true;
	}
}