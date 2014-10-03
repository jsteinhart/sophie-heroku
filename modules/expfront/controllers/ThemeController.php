<?php
class Expfront_ThemeController extends Symbic_Controller_Action
{
	public function assetAction()
	{
		$request = $this->getRequest();

		$theme = $request->getParam('theme', null);
		if (empty($theme))
		{
			echo 'Forbidden theme 403';
			exit;
		}

		$themeBasePath = BASE_PATH . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'sophie' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR;
		
		// TODO: check if $theme is alnum + '_'
		
		$themeAssetsPath = realpath($themeBasePath . $theme . DIRECTORY_SEPARATOR . 'assets');
		if ($themeAssetsPath === FALSE)
		{
			echo 'Forbidden theme 403';
			exit;
		}

		if (strpos($themeAssetsPath, $themeBasePath) !== 0)
		{
			echo 'Forbidden theme 403';
			exit;
		}
		
		$file = substr($request->getRequestUri(), strlen($request->getBaseUrl() . '/expfront/theme/asset/' . $theme));

		if (empty($file))
		{
			echo 'Forbidden empty file 403';
			exit;
		}

		// TODO: check if $file contains /../

		if (strrpos('/', $file) === strlen($file))
		{
			echo 'Forbidden directory index 403';
			exit;
		}

		$filePath = realpath( $themeAssetsPath . $file );
		if ($filePath === FALSE)
		{
			echo 'File not found 404';
			exit;		
		}

		if (strpos($filePath, $themeAssetsPath . DIRECTORY_SEPARATOR) !== 0)
		{
			echo 'File not found 404';
			exit;
		}

		$extPos = strrpos($file, '.');
		if ($extPos)
		{
			$fileExt = substr($file, $extPos + 1);
			switch ($fileExt)
			{
				case 'css':
					header('Content-type: text/css');
					break;

				case 'js':
					header('Content-type: text/javascript');
					break;
				
				case 'png':
					header('Content-type: image/png');
					break;

				case 'gif':
					header('Content-type: image/gif');
					break;

				case 'jpg':
				case 'jpeg':
					header('Content-type: image/jpeg');
					break;
					
				case 'html':
				case 'htm':
					header('Content-type: text/html');
					break;

				case 'txt':
					header('Content-type: text/plain');
					break;

				default:
					// DO NOT SEND A FILES WITHOUT A KNOWN EXT
					echo 'Forbidden 403';
					exit;
			}
		}
		else
		{
			// DO NOT SEND A FILE WITHOUT AN EXT
			echo 'Forbidden 403';
			exit;
		}

		// TODO: consider implementing an etag header?
		$cacheSec = 3600;
		header('Cache-Control: public, max-age=' . $cacheSec . ', s-maxage=' . $cacheSec, true);
		header('Expires: ' . gmdate("D, d M Y H:i:s", time() + $cacheSec) . ' GMT', true);
		header('Pragma: cache', true);

		if ($filePath)
		{
			@readfile($filePath);
		}
		else
		{
			echo '404';
			exit;
		}
		exit;
	}
}