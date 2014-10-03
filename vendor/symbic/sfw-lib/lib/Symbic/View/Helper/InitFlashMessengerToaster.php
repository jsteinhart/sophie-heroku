<?php
class Symbic_View_Helper_InitFlashMessengerToaster extends Zend_View_Helper_Abstract
{
	public function initFlashMessengerToaster($subscribeChannels = array('messages', 'errorMessages'))
	{
		$view = $this->view;

		// init toaster
		$view->headLink()->prependStylesheet($view->baseUrl( true ) . '/_scripts/symbic/Toaster.css', 'all');
		$view->dojo()->requireModule('symbic.Toaster');

		$toasterOptionsJson =  Zend_Json::encode(array('subscribe'=>$subscribeChannels));
		$view->dojo()->addOnLoad('function () { toaster = new symbic.Toaster(' . $toasterOptionsJson . '); }');

		// handle flash messages
		$flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
		$messages = $flashMessenger->getMessages();
		if ($flashMessenger->hasCurrentMessages()) {
			$messages = array_merge(
				$messages,
				$flashMessenger->getCurrentMessages()
			);
			$flashMessenger->clearCurrentMessages();
		}

		$i = 0;
		foreach ($messages as $message)
		{
			if (is_array($message)) {
				list($key,$message) = each($message);
			}
			if (!isset($key) || empty($key))
			{
				$key = 'message';
			}
			$view->dojo()->addOnLoad("function () { dojo.publish('messages', [" . json_encode(array('message'=>$message, 'type'=>$key)) . "] ); }");
			$i++;
		}


		return '<!-- Flash Messenger initialized with ' . $i . ' messages onLoad -->';
	}
}
