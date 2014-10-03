<?php

// PNotify - http://pinesframework.org/pnotify
// Include
//   jQuery
//   ../vendor/pnotify/1.2.0/jquery.pnotify.default.css
//   ../vendor/pnotify/1.2.0/jquery.pnotify.min.js
// In PHP
//   $content .= '$.pnotify({title: \'Bootstrap Info\', text: ' . json_encode($message) . ', type: \'info\', styling: \'bootstrap\' });';

// Alertify - http://fabien-d.github.io/alertify.js/
// Include
//   ../vendor/alertify/1.2.0/jquery.pnotify.default.css
//   ../vendor/alertify/1.2.0/jquery.pnotify.min.js
// In PHP
//   $content .= 'alertify.log(' . json_encode($message) . ', ' . json_encode($key) . ', 0);';

// Dojo Publish/Subscribe
// In PHP
//   $content .= 'dojo.publish(\'messages\', [' . json_encode( array('message' => $message, 'type' => $type) ) . '] );';

// Using Symbic Toaster for Backward Compatibility?
// Others:
// http://smoke-js.com/
// http://codeseven.github.io/toastr/
// http://ksylvest.github.io/jquery-growl/

class Symbic_View_Helper_JsFlashMessenger extends Zend_View_Helper_Abstract
{
	protected static $defaultFramework = 'pnotify';

	protected function getFlashMessenger()
	{
		return Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
	}

	protected function getCleanMessages()
	{
		$flashMessenger = $this->getFlashMessenger();
		$messages = $flashMessenger->getMessages();
		if ($flashMessenger->hasCurrentMessages())
		{
			$messages = array_merge(
				$messages,
				$flashMessenger->getCurrentMessages()
			);
			$flashMessenger->clearCurrentMessages();
		}
		return $messages;
	}	
	
	public function jsFlashMessenger()
	{
		return $this;
	}	
	
	public function renderPnotify()
	{
		$content = '';
		$messages = $this->getCleanMessages();
		
		if (sizeof($messages) > 0)
		{
			foreach ($messages as $message)
			{		
				if (is_array($message))
				{
					list($type, $message) = each($message);
				}

				if (!isset($type) || empty($type))
				{
					$type = 'info';
				}

				$content .= '$.pnotify({text: ' . json_encode($message) . ', type: ' . json_encode($type) . ', styling: "bootstrap" });';
			}
		}
		return $content;
	}
	
	public function render($useFramework = null)
	{
		if (is_null($useFramework))
		{
			$useFramework = self::$defaultFramework;
		}
		
		switch ($useFramework)
		{
			case 'pnotify':
				$content = $this->renderPnotify();
				break;
/*
			case 'alertify':
				$content = $this->renderAlertify();
				break;
			
			case 'dojo.publish':
				$content = $this->renderDojoPublish();
				break;
*/		
		}

		if (!empty($content))
		{
			$this->view->jsOnLoad()->appendScript($content);
		}
		return '';
	}
	
	public function __toString()
	{
		return $this->render();
	}
}