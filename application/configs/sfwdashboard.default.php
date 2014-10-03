<?php
return array(
	'headline' => 'Welcome to SoPHIE&#8212;the Software Platform for Human Interaction Experiments!',
	'blocks' => array(
		'experiments' => array(
			'type' => 'viewScript',
			'scriptFilename' => APPLICATION_PATH . '/sfwdashboard/blocks/experiments.phtml'
		),
		'sessions' => array(
				'type' => 'viewScript',
				'scriptFilename' => APPLICATION_PATH . '/sfwdashboard/blocks/sessions.phtml'
		)
	)
);