<?php
class Symbic_View_Helper_BaseUrl extends Zend_View_Helper_Abstract
{
	public function baseUrl( $includePath = false )
	{
		$https = !empty($_SERVER['HTTPS']);
		$protocol = $https ? 'https' : 'http';
		if (empty($_SERVER['HTTP_HOST']))
		{
			$server = $_SERVER['SERVER_NAME'];
			$port = ((!$https && $_SERVER['SERVER_PORT'] != 80) || ($https && $_SERVER['SERVER_PORT'] != 443)) ? (':' . $_SERVER['SERVER_PORT']) : '';
		}
		else
		{
			$server = $_SERVER['HTTP_HOST'];
			$port = '';
		}
		$path = ($includePath) ? rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') : '';
		return $protocol . '://' . $server . $port . $path;
	}
}