<?php
return array(
	'collections' => array(
		'default-css'	 => array(
			'type'		 => 'text/css',
			'assetFiles'	 => array(
				'bootstrap'	 => 'www/components/bootstrap/3.0.3/css/bootstrap.min.css',
				'select2'	 => 'www/components/select2/3.4.5/select2.css',
				'select2-bootstrap'	 => 'www/components/select2-bootstrap/3.4.5/select2-bootstrap.css',
				'bootstrap3-editable'	 => 'www/components/bootstrap3-editable/1.5.1/bootstrap3-editable/css/bootstrap-editable.css',
				'symbic-toaster' =>'www/_scripts/symbic/Toaster.css',
				'symbic-form' =>'www/_styles/symbic/form.css',
				'symbic-datatables' =>'www/_styles/symbic/datatables.css',
				'application-default' =>'www/_styles/application/default.css'
			)
		),
		'default-js'	 => array(
			'type'		 => 'text/javascript',
			'assetFiles'	 => array(
				'jquery' => 'www/components/jquery/1.10.2/jquery-1.10.2.min.js',
				'underscore' => 'www/components/underscore/1.6.0/underscore-min.js',
				'backbone' => 'www/components/backbone/1.1.2/backbone-min.js',
				'jquery-ui' => 'www/components/jquery-ui/1.10.4-custom/js/jquery-ui-1.10.4.custom.min.js',
				'jquery-ui-touch-punch' => 'www/components/jquery-ui-touch-punch/0.2.2/jquery.ui.touch-punch.min.js',
				'bootstrap' => 'www/components/bootstrap/3.0.3/js/bootstrap.min.js',
				'www/components/bootbox/4.1.0/bootbox.min.js',
				'select2' => 'www/components/select2/3.4.5/select2.js',
				'bootstrap3-editable' =>'www/components/bootstrap3-editable/1.5.1/bootstrap3-editable/js/bootstrap-editable.min.js',
				'jquery-autosize' =>'www/components/jquery-autosize/1.18.1/jquery.autosize.min.js',
				//'jquery-pnotify' => 'www/components/pnotify/1.2.0/jquery.pnotify.min.js',
				'jquery-datatables' => 'www/components/jquery-datatables/1.9.4/media/js/jquery.dataTables.min.js',
				'jquery-dirtyforms' => 'www/components/jquery-dirtyforms/master-commit47/jquery.dirtyforms.js',
				'jquery-cookie' => 'www/components/jquery-cookie/1.4.0/jquery.cookie.js',

				'symbic-backend' => 'www/_scripts/symbic/backend.js',

				'application-datatables-defaults' => 'www/_scripts/application/datatables-defaults.js',
				'application-backend' => 'www/_scripts/application/backend.js'
			)
		)
	)
);
