<?php

return array(
	'typeDefaults' => array(
		'text/css'		 => array(
			'filters'	 => array(
				'CssRewriteFilter' => array(
					'class' => '\Assetic\Filter\CssRewriteFilter'
				),
				/*'CssMinFilter'		 => array(
					'class' => '\Minifier\CssMin'
				),*/
			),
			'build'		 => array(
				'active'	 => true,
				'filePath'	 => BASE_PATH . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'assets',
				'fileExtension'	 => 'css'
			),
			'header'	 => array(
				'content-type' => 'text/css; charset=UTF-8'
			)
		),
		'text/javascript'	 => array(
			'filters'	 => array(
				/*'JsMinFilter' => array(
					'class' => 'Assetic\Filter\JsMinFilter'
				)*/
			),
			'build'		 => array(
				'active'	 => true,
				'filePath'	 => BASE_PATH . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'assets',
				'fileExtension'	 => 'js'
			),
			'header'	 => array(
				'content-type' => 'text/javascript; charset=UTF-8'
			)
		)
	)
);
