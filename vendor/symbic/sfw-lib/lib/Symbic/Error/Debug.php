<?php
/**
 *
 */
class Symbic_Error_Debug extends Symbic_Error_Display
{
	public function renderHtml($e)
	{
		$content = "<!DOCTYPE html>\n<html lang=\"en\"><body>";
		$content .= '<h1>Uncaught fatal application error</h1>';
		
		$content .= '<pre>';
		if (is_object($e) && $e instanceof \Exception)
		{
			// $e is an exception
			$content .= print_r($e, true);
		}
		else
		{
			// $e should be a php error
			$content .= print_r($e, true);
		}
		$content .= '</pre>';

		$content .= '</body></html>';

		return $content;
	}

	public function renderConsole($e)
	{
		$content = 'Uncaught fatal application error' . PHP_EOL;
		if ($e instanceof \Exception)
		{
			$content .= 'Exception: ' . $e->getMessage() . PHP_EOL;
			// $e is an exception
		}
		else
		{
			// $e should be a php error
			$content .= 'Error: ' . print_r($e, true) . PHP_EOL;
		}
		return $content;
	}
}
