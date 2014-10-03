<?php
return array(
	'login'	=> array(
		'pageTitle'	=> 'Welcome!',
		'headline'	=> 'Welcome!',
		'focusForm'	=> true,
		'redirectRouteLogin'	=> array (
			'preferCalledRoute'		=> true,
			'name'					=> 'home',
		),
		'redirectRouteLogout'	=> array (
			'name'					=> 'login',
		),
		'showNoScriptWarning'	=> true,
		'rememberMe'			=> array (
			'active'				=> false,
			'remember'				=> 'loginOnly',
			'loginCookieName'		=> 'sfwloginLoginRememberMeLogin',
			'cookieExpireDays'		=> 14,
		),

		'throttleFailedLogin'	=> array (
			'active'				=> false,
			'baseDuration'			=> 2000000,
		),

		'hash'					=> array (
			'active'				=> false
		),

		'captcha'				=> array (
			'active'				=> false,
			'trigger'				=> 'always'
		)
	),

	'forgotPassword' => array(
		'active' => false,
		'validUntilExpr' => '+2 day',

		'hash'					=> array (
			'active'				=> false
		),

		'captcha'		=> array (
			'active'				=> false,
			'trigger'				=> 'always'
		),

		'fallbackUserName'=> 'User',
		'mailSubject' => 'Password reset',
		'mailTemplateText' => 'Hello {{ userName }},

Forgot your password? We just received a request to reset it. To verify that you made this request, we\'re sending this confirmation email.

To reset your password please copy the following link into your browser:
{{ linkReset }}

The link is valid until {{ validUntilFormated }}.

Thank you!

Sincerely',
		'mailTemplateHtml' => 'Hello {{ userName }},<br />
<br />
Forgot your password? We just received a request to reset it. To verify that you made this request, we\'re sending this confirmation email.<br />
<br />
To reset your password please click on the following link:<br />
<a href="{{ linkReset }}">Reset password</a><br />
<br />
Alternatively, you may also copy/paste the following link into your browser: {{ linkReset }}<br />
<br />
The link is valid until {{ validUntilFormated }}.<br />
<br />
Thank you!<br />
<br />
Sincerely<br />'
	)
);
