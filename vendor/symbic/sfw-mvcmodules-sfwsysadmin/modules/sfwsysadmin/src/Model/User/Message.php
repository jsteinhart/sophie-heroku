<?php
namespace Sfwsysadmin\Model\User;

class Message
{
	public function sendMessageToUser($senderName, $senderEmail, $user, $subject, $bodyText, $bodyTextFooter, $messageReplacements)
	{
		if(empty($user['email']))
		{
			throw new \Exception('Failed to send email: no email address');
		}

		$mail = new \Zend_Mail('UTF-8');
		$mail->setFrom($senderEmail, $senderName);
		$mail->addTo($user['email'], $user['name']);

		$m = new \Mustache_Engine();
		$bodyText .= "\n" . $bodyTextFooter;
		$subject = $m->render($subject, $messageReplacements);
		$bodyText = $m->render($bodyText, $messageReplacements);

		$mail->setSubject($subject);
		$mail->setBodyText($bodyText);

		try
		{
			$mail->send();
		}
		catch(\Exception $e)
		{
			throw new \Exception('Failed to send email to ' . $user['name']);
		}
	}
}