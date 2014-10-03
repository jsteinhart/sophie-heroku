<?php
return array(	
	'autoloadNamespaces' => array(
		'Application_Task_' => APPLICATION_PATH . DIRECTORY_SEPARATOR . 'tasks'
	),
	'runOnlyConfiguredTasks' => false,
	'tasks' => array(
		'updateDb'		=> array(
			'name' 			=> 'Symbic_Task_UpdateDb',
			'parameters'	=> array(
				'updatesClass'				=> 'Application_Contrib_Updates',
				'autoloadUpdatesClass'		=> false,
				'updatesClassFile'			=> BASE_PATH . '/contrib/Updates.php'
			)
		),
		'test'			=> array(
			'name' 			=> 'Symbic_Task_Test'
		)
	),
	'groups' => array(
		'test' => array(
			'tasks' => array(
				'test'
			)
		)
	)
);