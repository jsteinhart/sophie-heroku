<?php
class Sfwlogin_CaptchaController extends Symbic_Controller_Action
{
	public function refreshAction()
	{		
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();

		$response->setHeader('cache-control', 'no-store');
		$response->setHeader('expires', gmdate("D, d M Y H:i:s", 1) . ' GMT');
		$response->setHeader('Content-type', 'image/jpeg');

		$type = $this->getParam('type', null);
		$moduleConfig = $this->getModule()->getModuleConfig();

		if ($type === 'login' && isset($moduleConfig['login']) && isset($moduleConfig['login']['captcha']))
		{
			$captchaConfig = $moduleConfig['login']['captcha'];
			if (isset($captchaConfig['active']) && $captchaConfig['active'] === true)
			{
				$captchaBuilder = new Gregwar\Captcha\CaptchaBuilder();
				$controllerSession = new Zend_Session_Namespace(Sfwlogin_LoginController::SESSION_NAMESPACE);
				$controllerSession->captchaPhrase = $builder->getPhrase();
				$captchaBuilder->output();
			}
		}
		
		elseif ($type === 'forgotPassword' && isset($moduleConfig['forgotPassword']) && isset($moduleConfig['forgotPassword']['captcha']))
		{
			$captchaConfig = $moduleConfig['forgotPassword']['captcha'];
			
			$captchaConfig = $moduleConfig['login']['captcha'];
			if (isset($captchaConfig['active']) && $captchaConfig['active'] === true)
			{
				$captchaBuilder = new Gregwar\Captcha\CaptchaBuilder();
				$controllerSession = new Zend_Session_Namespace(Sfwlogin_ForgotpasswordController::SESSION_NAMESPACE);
				$controllerSession->captchaPhrase = $builder->getPhrase();
				$captchaBuilder->output();
			}
		}
		
		elseif ($type === 'forgotPasswordReset' && isset($moduleConfig['forgotPasswordReset']) && isset($moduleConfig['forgotPasswordReset']['captcha']))
		{
			$captchaConfig = $moduleConfig['forgotPasswordReset']['captcha'];

			$captchaConfig = $moduleConfig['login']['captcha'];
			if (isset($captchaConfig['active']) && $captchaConfig['active'] === true)
			{
				$captchaBuilder = new Gregwar\Captcha\CaptchaBuilder();
				$controllerSession = new Zend_Session_Namespace(Sfwlogin_ForgotpasswordController::SESSION_NAMESPACE);
				$controllerSession->captchaPhraseReset = $builder->getPhrase();
				$captchaBuilder->output();
			}
		}		
	}
