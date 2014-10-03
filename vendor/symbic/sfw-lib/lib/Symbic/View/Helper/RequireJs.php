<?php
class Symbic_View_Helper_RequireJs extends Zend_View_Helper_Abstract
{
	protected static $requireScript = '/components/requirejs/2.1.11/require.js';
	protected static $mainScript = null;
	
	protected static $config = array(
		'baseUrl' => 'components',
		'paths' => array(
			'backbone'				=> 'backbone/1.1.2/backbone-min',
			'bootbox'				=> 'bootbox/4.1.0/bootbox.min',
			'bootstrap'				=> 'bootstrap/3.0.3/js/bootstrap.min',
			'bootstrap3-editable'	=> 'bootstrap3-editable/1.5.1/bootstrap3-editable/bootstrap3-editable.min',
			'codemirror'			=> 'codemirror/3.22-build/codemirror-compressed',
			'jquery'				=> 'jquery/1.10.2/jquery-1.10.2.min',
			'jquery.autosize'		=> 'jquery-autosize/1.18.1/jquery.autosize.min',
			'jquery.cookie'			=> 'jquery-cookie/1.4.0/jquery.cookie',
			'jquery.dataTables'		=> 'jquery-datatables/1.9.4/media/js/jquery.dataTables.min',
			'jquery.dirtyforms'		=> 'jquery-dirtyforms/master-commit47/jquery.dirtyforms.js',
			'jquery.handsontable'	=> 'jquery-handsontable/0.10.2/dist/jquery.handsontable.full',
			'jquery.pnotify'		=> 'jquery-pnotify/1.2.0/jquery.pnotify.min',
			'jquery.ui'				=> 'jquery-ui/1.10.4-custom/js/jquery-ui-1.10.4.custom.min',
			'jquery.ui.touch-punch'	=> 'jquery-ui-touch-punch/0.2.2/jquery.ui.touch-punch.min',
			'radio'					=> 'radio/0.2.0/radio.min',
			'select2'				=> 'select2/3.4.5/select2.min',
			'underscore'			=> 'underscore/1.6.0/underscore-min'
		),
		'shim' => array(
			'backbone' => array(
				'deps' => array('underscore', 'jquery'),
				'exports' => 'Backbone'
			),
			'bootbox' => array(
				'deps' => array('jquery', 'bootstrap'),
				'exports' => 'bootbox'
			),
			'bootstrap' => array(
				'deps' => array('jquery')
			),
			'bootstrap3-editable' => array(
				'deps' => array('jquery', 'bootstrap')
			),
			'jquery.autosize' => array(
				'deps' => array('jquery')
			),
			'jquery.cookie' => array(
				'deps' => array('jquery')
			),
			'jquery.dataTables' => array(
				'deps' => array('jquery')
			),
			'jquery.dirtyforms' => array(
				'deps' => array('jquery')
			),
			'jquery.pnotify' => array(
				'deps' => array('jquery')
			),
			'jquery.ui' => array(
				'deps' => array('jquery')
			),
			'jquery.ui.touch-punch' => array(
				'deps' => array('jquery', 'jquery.ui')
			),
			'bootbox' => array(
				'deps' => array('jquery', 'bootstrap'),
				'exports' => 'bootbox'
			),
			'select2' => array(
				'deps' => array('jquery')
			),
			'underscore' => array(
				'exports' => '_'
			)
		)
	);

	public static function getConfig()
	{
		return self::$config;
	}

	public static function setConfig($config)
	{
		self::$config = $config;
	}

	public function requireJs()
	{
		return $this;
	}
	
	public function getRenderedConfig()
	{
		return 'requirejs.config(' . json_encode(self::getConfig()) . ');';
	}
	
	public function __toString()
	{
		$content = '<script';
		if (!empty(self::$mainScript))
		{
			$content .= ' data-main="' . self::$mainScript . '"';
		}
		$content .= ' src="' . self::$requireScript .'"></script>';
		
		return $content;
	}
}