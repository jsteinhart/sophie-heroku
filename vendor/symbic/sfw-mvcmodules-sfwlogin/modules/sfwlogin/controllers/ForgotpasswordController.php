<?php
class Sfwlogin_ForgotpasswordController extends Symbic_Controller_Action
{
	private $userSession;
	private $userService;
	private $moduleConfig;
	protected $controllerSession;

	const SESSION_NAMESPACE = 'Sfwlogin_ForgotpasswordControllerSession';

	public function init()
	{
		$this->userSession = $this->getUserSession();
		$this->userService = Symbic_User_Service::getInstance();
		$this->moduleConfig = $this->getModuleConfig();

		$this->controllerSession = new Zend_Session_Namespace(self::SESSION_NAMESPACE);
	}

	public function preDispatch()
	{
		if (!isset($this->moduleConfig['forgotPassword']) || !isset($this->moduleConfig['forgotPassword']['active']) || $this->moduleConfig['forgotPassword']['active'] == 0)
		{
			$this->_error('Forgot password feature is not available');
		}

		if ($this->userSession->isLoggedIn())
		{
			$this->_error('You are logged in. Please use the userprofile to change your password.');
		}

		$this->view->translator = $this->getModule()->getTranslator();;
	}

	public function indexAction()
	{
		// TODO: check forgot password ACLs (IP subnets, geo ip, https)

		$form = $this->getForm('Forgotpassword_Login');

		if (isset($this->moduleConfig['forgotPassword']['captcha']) && isset($this->moduleConfig['forgotPassword']['captcha']['active']) && $this->moduleConfig['forgotPassword']['captcha']['active'] === true)
		{
			// TODO: implement trigger or limit events to conditionally activate captcha
			if ($this->moduleConfig['forgotPassword']['captcha']['trigger'] == 'always')
			{
				$captchaElement = $form->getElement('captcha');
				$captchaElement->setRefreshUrl($this->view->url(array('controller' => 'captcha', 'action' => 'refresh', 'type' => 'forgotPasswordLogin')));
			}
			else
			{
				$form->removeElement('captcha');
			}
		}
		else
		{
			$form->removeElement('captcha');
		}

		// check if hash protection is enabled
		if (!isset($this->moduleConfig['forgotPassword']['hash']) || !isset($this->moduleConfig['forgotPassword']['hash']['active']) || $this->moduleConfig['forgotPassword']['hash']['active'] !== true)
		{
			$form->removeElement('hash');
			$hashActive = false;
		}

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();

				// TODO: check if limits are exceeded (number of resets per period)

				$loginElement = $form->getElement('login');

				// fetch user
				$userServiceClass = get_class($this->userService);
				$user = $this->userService->fetchActiveUserByLogin($values['login']);
				$fetchUserStatus = $this->userService->getLastFetchUserByLoginStatus();
				if ($fetchUserStatus === $userServiceClass::FETCH_USER_UNKNOWN)
				{
					$loginElement->addError('Login does not exists. If you have forgotten your login please contact the system administrator.');
				}
				elseif ($fetchUserStatus === $userServiceClass::FETCH_USER_DEACTIVATED)
				{
					$loginElement->addError('Cannot use forgot password feature because the user is deactivated. Please contact the system administrator.');
				}
				elseif ($fetchUserStatus !== $userServiceClass::FETCH_SUCCESSFUL)
				{
					$loginElement->addError('Unexpected error occured when fetching user for forgot password feature.');
				}
				else
				{
					if (empty($user['email']))
					{
						$loginElement->addError('Cannot use forgot password feature because the user profile does not contain an email address. Please contact the system administrator.');
					}
					else
					{
						// Send mail
						$userModel = $this->userService->getLastFetchUserByLoginModel();
						if (!method_exists($userModel, 'generateForgotPasswordTokenById'))
						{
							$loginElement->addError('The forgot password feature is not supported for this user. Please contact the system administrator.');
						}
						else
						{
							if (empty($this->moduleConfig['forgotPassword']['validUntilExpr']))
							{
								throw new Exception('validUntilExpr config setting is missing');
							}
							$validUntil = strtotime($this->moduleConfig['forgotPassword']['validUntilExpr']);

							// TODO: handle exceptions from loginModel gracefully?
							$token = $userModel->generateForgotPasswordTokenById($user['id'], $validUntil);
							
							if (empty($token))
							{
								$loginElement->addError('Generating a forgot password token failed. Please try again later or contact the system administrator.');
							}
							else
							{
								if (empty($user['name']))
								{
									if (!empty($this->moduleConfig['forgotPassword']['fallbackUserName']))
									{
										$user['name'] = $this->moduleConfig['forgotPassword']['fallbackUserName'];
									}
									else
									{
										$user['name'] = 'User';
									}
								}

								$linkReset = $this->view->baseUrl() . $this->view->url(array('action' => 'reset', 'login' => $user['login'], 'token' => $token));
								
								$mailModel = $this->getModule()->getModelSingleton('Forgotpassword_Mail');
								$mailResult = $mailModel->sendChangePasswordMail($user['login'], $user['name'], $user['email'], $token, $validUntil, $linkReset, $this->moduleConfig['forgotPassword']);
								if ($mailResult === true)
								{
									$this->_helper->getHelper('Redirector')->gotoRoute(array('action' => 'mailsent'));
									return;
								}
								$loginElement->addError('Sending forgot password email failed. Please contact the system administrator.');

							}
						}
					}
				}
			}
			else
			{
				// TODO: add error message to form if hash has expired
				/*
				if ($hashActive === true)
				{
					
					$hash = $form->getElement('hash');
					if ($hash->isValid())
					{
					}
				}*/
			}
		}

		$this->view->form = $form;
	}

	public function mailsentAction()
	{
	}

	public function resetAction()
	{
		$login = $this->_getParam('login', '');
		$token = $this->_getParam('token', '');
		$form = $this->getForm('Forgotpassword_Reset');

		$form->setDefaults(
			array(
				'login' => $login,
				'token' => $token
			)
		);

		if (isset($this->moduleConfig['forgotPasswordReset']['captcha']) && isset($this->moduleConfig['forgotPasswordReset']['captcha']['active']) && $this->moduleConfig['forgotPasswordReset']['captcha']['active'] === true)
		{
			// TODO: implement trigger or limit events to conditionally activate captcha
			if ($this->moduleConfig['forgotPasswordReset']['captcha']['trigger'] == 'always')
			{
				$captchaElement = $form->getElement('captcha');
				$captchaElement->setRefreshUrl($this->view->url(array('module' => $this->getModule()->getModuleName(), 'controller' => 'captcha', 'refresh' => 'refresh', 'type' => 'forgotpasswordReset')));
			}
			else
			{
				$form->removeElement('captcha');
			}
		}
		else
		{
			$form->removeElement('captcha');
		}

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();

				// fetch user
				$userServiceClass = get_class($this->userService);
				$user = $this->userService->fetchUserByLogin($values['login']);

				$loginElement = $form->getElement('login');

				$fetchUserStatus = $this->userService->getLastFetchUserByLoginStatus();
				if ($fetchUserStatus === $userServiceClass::FETCH_USER_UNKNOWN)
				{
					$loginElement->addError('Login does not exists. If you have forgotten your login please contact the system administrator.');
				}
				elseif ($fetchUserStatus === $userServiceClass::FETCH_USER_DEACTIVATED)
				{
					$loginElement->addError('Cannot use forgot password feature because the user is deactivated. Please contact the system administrator.');
				}
				elseif ($fetchUserStatus !== $userServiceClass::FETCH_SUCCESSFUL)
				{
					$loginElement->addError('Unexpected error occured when fetching user for forgot password feature.');
				}
				else
				{
					$userModel = $this->userService->getLastFetchUserByLoginModel();
					$checkTokenResult = $userModel->checkForgotPasswordToken($values['token'], $values['login']);

					if ($checkTokenResult === $userServiceClass::TOKEN_VALID)
					{
						$setPasswordResult = $userModel->setPasswordById($user['id'], $values['password']);

						if ($setPasswordResult === true)
						{
							$userModel->setForgotPasswordTokenState($values['token'], 'used');
							$userModel->invalidateUnusedForgotPasswordTokensByLogin($values['login']);
							
							$this->_helper->getHelper('Redirector')->gotoRoute(array('module' => 'sfwlogin', 'controller' => 'forgotpassword', 'action' => 'resetsuccess'), 'default', true);
							return;
						}
						else
						{
							$passwordElement = $form->getElement('password');
							$passwordElement->addError('System failure while setting a the new password.');
						}
					}
					elseif ($checkTokenResult === $userServiceClass::TOKEN_EXPIRED)
					{
						$tokenElement = $form->getElement('token');
						$tokenElement->addError('The token has expired or has already been used to reset your password.');
					}
					else
					{
						$tokenElement = $form->getElement('token');
						$tokenElement->addError('Invalid or incomplete token. Please check if you have entered the token completely.');
					}
				}
			}
		}
		
		$this->view->form = $form;
	}

	public function resetsuccessAction()
	{
	}
}