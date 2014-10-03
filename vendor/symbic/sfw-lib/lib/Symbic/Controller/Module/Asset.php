<?php
class Symbic_Controller_Module_Asset extends Symbic_Controller_Action
{
	protected function badrequestResponse()
	{
		$this->getResponse()->setHttpResponseCode(400);
		echo 'Bad Request Error';
		exit;
	}

	protected function forbiddenResponse()
	{
		$this->getResponse()->setHttpResponseCode(403);
		echo 'Forbidden';
		exit;
	}

	protected function notfoundResponse()
	{
		$this->getResponse()->setHttpResponseCode(404);
		echo 'File not found error';
		exit;
	}

	public function indexAction()
	{
		$file = $this->_getParam('file', null);
		if (empty($file))
		{
			$this->notfoundResponse();
			return;
		}

		$moduleAssetDir = $this->getModuleDir() . DIRECTORY_SEPARATOR . 'assets';
		if (!file_exists($moduleAssetDir) || ! is_dir($moduleAssetDir))
		{
			$this->notfoundResponse();
			return;
		}

		$filePath = $moduleAssetDir . DIRECTORY_SEPARATOR . $file;
		$filePath2 = @realpath($filePath);
		if ($filePath2 === false)
		{
			if (!file_exists($filePath))
			{
				$this->notfoundResponse();
				return;
			}
			else
			{
				// Directory traversal?
				// TODO: log security event?
				$this->invalid();
				return;
			}
		}

		if (strpos($filePath2, $moduleAssetDir) != 0)
		{
			// Directory traversal?
			// TODO: log security event?
			$this->forbiddenResponse();
			return;
		}

		//get the last-modified-date of this very file
		$lastModified=filemtime($filePath2);

		//get a unique hash of this file (etag)
		$etagFile = md5_file($filePath2);

		//get the HTTP_IF_MODIFIED_SINCE header if set
		$ifModifiedSince=(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false);

		//get the HTTP_IF_NONE_MATCH header if set (etag: unique file hash)
		$etagHeader=(isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);

		//set last-modified header
		header("Last-Modified: ".gmdate("D, d M Y H:i:s", $lastModified)." GMT");

		//set etag-header
		header('Etag: ' . $etagFile);

		//make sure caching is turned on    
		//check if page has changed. If not, send 304 and exit
		if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified || $etagHeader == $etagFile)
		{
			   header("HTTP/1.1 304 Not Modified");
			   exit;
		}

		$extPos = strpos($file, '.');
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
					$this->forbiddenResponse();
					return;
			}
		}
		else
		{
			// DO NOT SEND A FILES WITHOUT EXT
			$this->forbiddenResponse();
			return;
		}

		// TODO: consider a per file config for headers and expiry
		// TODO: remove this in favor of etag?
		$cacheSec = 3600;
		header('Cache-Control: public, max-age=' . $cacheSec . ', s-maxage=' . $cacheSec, true);
		header('Expires: ' . gmdate("D, d M Y H:i:s", time() + $cacheSec) . ' GMT', true);
		header('Pragma: cache', true);

		@readfile($filePath);
		exit;
	}
}