<?php
class Sfwlogin_LoginController extends Symbic_Controller_Action
{
	protected $userSession;
	protected $userService;
	protected $moduleConfig;
	protected $loginConfig;
	protected $forgotPasswordConfig;
	protected $controllerSession;

	const SESSION_NAMESPACE = 'Sfwlogin_LoginControllerSession';

	public function init()
	{
		$this->userSession = $this->getUserSession();
		$this->userService = Symbic_User_Service::getInstance();
		$this->moduleConfig = $this->getModuleConfig();

		if (!isset($this->moduleConfig['login']) || !is_array($this->moduleConfig['login']))
		{
			$this->moduleConfig['login'] = array();
		}
		$this->loginConfig = $this->moduleConfig['login'];

		if (!isset($this->moduleConfig['forgotPassword']) || !is_array($this->moduleConfig['forgotPassword']))
		{
			$this->moduleConfig['forgotPassword'] = array();
		}
		$this->forgotPasswordConfig = $this->moduleConfig['forgotPassword'];

		$this->controllerSession = new Zend_Session_Namespace(self::SESSION_NAMESPACE);
	}

	protected function gotoPostLoginRoute()
	{
		if (isset($this->loginConfig['redirectRouteLogin']) && !empty($this->loginConfig['redirectRouteLogin']['preferCalledRoute']) && $this->controllerSession->redirectCounter < 1)
		{
			$this->controllerSession->redirectCounter++;
			$this->_helper->getHelper('Redirector')->gotoUrl($this->_helper->url->url());
			return;
		}
		if (!isset($this->loginConfig['redirectRouteLogin']) || !isset($this->loginConfig['redirectRouteLogin']['values']))
		{
			$values = array();
		}
		else
		{
			$values = (array)$this->loginConfig['redirectRouteLogin']['values'];
		}
		if (!isset($this->loginConfig['redirectRouteLogin']) || !isset($this->loginConfig['redirectRouteLogin']['name']))
		{
			$route = 'default';
		}
		else
		{
			$route = (string)$this->loginConfig['redirectRouteLogin']['name'];
		}
		$this->_helper->getHelper('Redirector')->gotoRoute($values, $route);
	}

	public function indexAction()
	{
		// do not interfere if a user session is valid
		// protection against compromised sessions should be handled somewhere else
		if ($this->userSession->isLoggedIn())
		{
			// TODO: use an alternative page showing logout and go to start page links?
			$this->gotoPostLoginRoute();
			return;
		}

		// TODO: onBeforeLoginForm($this->userSession)

		// reset redirect counter
		$this->controllerSession->redirectCounter = 0;

		// create form
		$form = $this->getForm('Login');

		// remove unused form elements
		if (!isset($this->forgotPasswordConfig['active']) || $this->forgotPasswordConfig['active'] !== true)
		{
			$passwordElement = $form->getElement('password');
			$passwordElement->setExtra('input-group-append', null);
		}

		if (!isset($this->loginConfig['rememberMe']) || !isset($this->loginConfig['rememberMe']['active']) || $this->loginConfig['rememberMe']['active'] !== true)
		{
			$form->removeElement('rememberMe');
		}

		if (isset($this->loginConfig['captcha']) && isset($this->loginConfig['captcha']['active']) && $this->loginConfig['captcha']['active'] === true)
		{
			// TODO: implement trigger or limit events to conditionally activate captcha
			if ($this->loginConfig['captcha']['trigger'] == 'always')
			{
				$captchaActive = true;
			}
			else
			{
				$captchaActive = false;
			}
		}
		else
		{
			$captchaActive = false;
		}

		if ($captchaActive === true)
		{
			$captchaElement = $form->getElement('captcha');
			$captchaElement->setRefreshUrl($this->view->url(array('controller' => 'captcha', 'action' => 'refresh', 'type' => 'login')));
		}
		else
		{
			$form->removeElement('captcha');
		}

		// check if hash protection is enabled
		if (!isset($this->loginConfig['hash']) || !isset($this->loginConfig['hash']['active']) || $this->loginConfig['hash']['active'] !== true)
		{
			$form->removeElement('hash');
			$hashActive = false;
		}
		else
		{
			$hashActive = true;
		}

		// use parameter as default value for login
		$defaults = array(
			'login' => $this->_getParam('login', ''),
		);

		// TODO: onUrlDefaultLogin($defaults['login'], $this->userSession)

		// use rememberMeLogin cookie
		if (isset($this->loginConfig['rememberMe']) && isset($this->loginConfig['rememberMe']['active']) && $this->loginConfig['rememberMe']['active'] && isset($this->loginConfig['rememberMe']['loginCookieName']) && !empty($this->loginConfig['rememberMe']['loginCookieName']))
		{
			$cookieLogin = $this->getRequest()->getCookie($this->loginConfig['rememberMe']['loginCookieName'], '');

			// TODO: onRememberMeLogin($cookieLogin, $this->userSession)
			if ($cookieLogin != '')
			{
				$defaults['rememberMe'] = '1';
			}

			//  only use cookie supplied login if no login url parameter is given
			if ($defaults['login'] == '')
			{
				$defaults['login'] = $cookieLogin;
			}
		}

		// set form action and default values
		$form->setAction($this->_helper->url->url());
		$form->setDefaults($defaults);

		// process form
		if ($this->getRequest()->isPost())
		{
			ignore_user_abort();

			// TODO: onFormPost($this->userSession)

			if ($form->isValid($_POST))
			{
				$values = $form->getValues();

				// TODO: onValidFormPost($values['login'], $values['password'], $this->userSession)

				// TODO: checkLoginByLoginAndPassword to run onBeforeValidLogin
				// TODO: check post login network ACL (IP subnets, geo ip, https): separate check login and login

				if ($this->userService->loginByLoginAndPassword($values['login'], $values['password'], $this->userSession))
				{
					if (!$this->userSession->isLoggedIn())
					{
						$this->userSession->logout();
						// TODO: add error to login form instead or use Exception to allow for better error handling
						$this->_error('User model indicated successful login but no session user data set');
						return;
					}

					if (isset($this->loginConfig['rememberMe']) && isset($this->loginConfig['rememberMe']['active']) && $this->loginConfig['rememberMe']['active'] && isset($this->loginConfig['rememberMe']['loginCookieName']) && !empty($this->loginConfig['rememberMe']['loginCookieName']) && $values['rememberMe'] === '1')
					{
						if (isset($this->loginConfig['rememberMe']['cookieExpireDays']) && $this->loginConfig['rememberMe']['cookieExpireDays'] > 0)
						{
							$expireDays = (int)$this->loginConfig['rememberMe']['cookieExpireDays'];
						}
						else
						{
							$expireDays = 14;
						}
						$expire = time() + (3600 * 24) * $expireDays;

						setcookie($this->loginConfig['rememberMe']['loginCookieName'], $values['login'], $expire);
					}
					else
					{
						setcookie($this->loginConfig['rememberMe']['loginCookieName'], '', 0);
					}

					if (isset($this->loginConfig['postLoginTasks']))
					{
						if (!is_array($this->loginConfig['postLoginTasks']))
						{
							$this->userSession->logout();
							throw new Exception('Broken login configuration');
						}

						foreach ($this->loginConfig['postLoginTasks'] as $postLoginTask)
						{
							switch ($postLoginTask['type'])
							{
								case 'include':
									if (file_exists($postLoginTask['file']))
									{
										include $postLoginTask['file'];
									}
									else
									{
										throw new Exception('postLoginTask include file not found');
									}
									break;

								default:
									throw new Exception('Unknown postLoginTask found');
							}
						}
					}

					$this->gotoPostLoginRoute();
					return;
				}

				$userServiceClass = get_class($this->userService);

				$loginStatus = $this->userService->getLastLoginByLoginAndPasswordStatus();

				switch ($loginStatus)
				{
					case $userServiceClass::LOGIN_USER_UNKNOWN:
					case $userServiceClass::LOGIN_WRONG_PASSWORD:
						$form->getElement('login')->addError('User does not exist ...');
						$form->getElement('password')->addError('... or invalid password');
						break;

					case $userServiceClass::LOGIN_USER_DEACTIVATED:
						$form->getElement('login')->addError('User is deactivated. Please contact the system administrator.');
						break;

					case $userServiceClass::MODEL_FAILURE:
						$form->getElement('login')->addError('User login system failed. This may be a temporary error. Please try again later or contact the system administrator.');
						break;

					default:
						throw new Exception('Unexpected return of user login');
						break;
				}

				// TODO: log unsuccessful login attempt: use hash of login if user unknown to prevent plain text logging of password input in login field

				$potentialAttackStates = array($userServiceClass::LOGIN_USER_UNKNOWN, $userServiceClass::LOGIN_WRONG_PASSWORD, $userServiceClass::LOGIN_USER_DEACTIVATED);

				if (in_array($loginStatus, $potentialAttackStates))
				{
					// slow down potential brute-force attacks
					if (isset($this->loginConfig['throttleFailedLogin']) && isset($this->loginConfig['throttleFailedLogin']['active']) && $this->loginConfig['throttleFailedLogin']['active'])
					{
						if (isset($this->loginConfig['throttleFailedLogin']['baseDuration']))
						{
							$throttleFailedLoginBaseDuration = (int)$this->loginConfig['throttleFailedLogin']['baseDuration'];
						}
						else
						{
							$throttleFailedLoginBaseDuration = 2000;
						}
						// WARNING: this is keeping the process busy, better to use a stateful rate-limit and to reject or delay requests within enforced waiting period
						usleep($throttleFailedLoginBaseDuration);
					}
				}
			}
			else
			{
				// add error message to form if hash has expired
				if ($hashActive === true)
				{
					$hashElement = $form->getElement('hash');
					if (!$hashElement->isValid())
					{
						$form->getElement('login')->addError('Your login session has expired. Please try to login again.');
					}
				}

				// TODO: onInvalidFormPost($values['login'], $values['password'], $this->userSession)
			}
		}

		if (!empty($this->loginConfig['focusForm']) && $this->loginConfig['focusForm'])
		{
			if ($defaults['login'] != '')
			{
				$form->getElement('password')->setAttrib('autofocus', 'autofocus');
			}
			else
			{
				$form->getElement('login')->setAttrib('autofocus', 'autofocus');
			}
		}

		$translator = $this->getModule()->getTranslator();
		$this->view->translator = $translator;
		$this->view->loginConfig = $this->loginConfig;

		$this->view->left = '';
		$this->view->right = '';

		$form->getElement('password')->setValue('');
		$this->view->form = $form;
	}
}