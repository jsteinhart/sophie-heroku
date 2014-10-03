<?php

class Sfwassets_CollectionController extends Symbic_Controller_Action
{

	public function indexAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();

		$name = $this->getParam('name', null);
		if (!preg_match("/^[a-zA-Z0-9_\-]+$/", $name))
		{
			$this->_error('Invalid collection name');
			return;
		}

		$moduleConfig = $this->getModule()->getModuleConfig();

		if (!isset($moduleConfig['collections']) || !is_array($moduleConfig['collections']) || !isset($moduleConfig['collections'][$name]))
		{
			$this->getResponse()->setHttpResponseCode(404);
			echo 'File not found';
			return;
		}

		$collectionConfig = $moduleConfig['collections'][$name];

		if (isset($collectionConfig['type']) && isset($moduleConfig['typeDefaults'][$collectionConfig['type']]))
		{
			$collectionConfig = array_replace_recursive($collectionConfig, $moduleConfig['typeDefaults'][$collectionConfig['type']]);
		}

		// TODO: implement development mode
		if (!isset($moduleConfig['cache']) || !isset($moduleConfig['cache']['active']) || $moduleConfig['cache']['active'] == 1)
		{
			if (!isset($collectionConfig['cache']) || !isset($collectionConfig['cache']['active']) || $collectionConfig['cache']['active'] == 1)
			{
				$cache = Zend_Registry::get('Zend_Cache');	
				$collectionContent = $cache->load('sfwassetsCollection_' . md5($name));
			}
		}
		else
		{
			$collectionContent = false;
		}
		$collectionContent = false;

		if (!$collectionContent)
		{
			$collection = new Assetic\Asset\AssetCollection();

			if (isset($collectionConfig['assetFiles']) && is_array($collectionConfig['assetFiles']))
			{
				if (isset($collectionConfig['assetFileBasePath']))
				{
					$assetFileBasePath = $collectionConfig['assetFileBasePath'];
				}
				else
				{
					$assetFileBasePath = BASE_PATH;
				}

				$assetFileBasePath2 = realpath($assetFileBasePath);
				if ($assetFileBasePath2 === false)
				{
					throw new Exception('Asset File Base Path not found');
				}
				$assetFileBasePath2 .= DIRECTORY_SEPARATOR;

				foreach ($collectionConfig['assetFiles'] as $assetFile)
				{
					if (substr($assetFile, 0, 1) !== '/')
					{
						$assetFile = $assetFileBasePath2 . $assetFile;
					}

					$assetFile2 = realpath($assetFile);
					
					if ($assetFile2 === false)
					{
						throw new Exception('Asset File not found: ' . $assetFile);
					}
					
					try
					{
						// TODO: implement file specific filters
						// TODO: implement file config for root and path
						$assetFileSourceRoot = BASE_PATH . DIRECTORY_SEPARATOR . 'www';
						$assetFileSourcePath = str_replace(DIRECTORY_SEPARATOR, '/', substr($assetFile2, strlen($assetFileSourceRoot)));

						$asset = new Assetic\Asset\FileAsset($assetFile2, array(), $assetFileSourceRoot, $assetFileSourcePath);
					}
					catch (Exception $e)
					{
						trigger_error('Loading asset ' . $assetFile2 . ' failed');
						continue;
					}

					$collection->add($asset);
				}
			}

			if (isset($collectionConfig['assetUrls']) && is_array($collectionConfig['assetUrls']))
			{
				foreach ($collectionConfig['assetUrls'] as $assetUrl)
				{
					try
					{
						$asset = new Assetic\Asset\HttpAsset($assetUrl);
					}
					catch (Exception $e)
					{
						trigger_error('Loading asset ' . $assetUrl . ' failed');
						continue;
					}

					$collection->add($asset);
				}
			}

			
			if (isset($collectionConfig['filters']) && sizeof($collectionConfig['filters']) > 0)
			{
				$filters = array();
				foreach ($collectionConfig['filters'] as $filterName => $filterOptions)
				{
					if (!empty($filterOptions['class']))
					{
						$filters = new $filterOptions['class']();
					}
				}
				$collectionContent = $collection->dump($filters);
			}
			else
			{
				$collectionContent = $collection->dump();
			}

			$cache->save('sfwassetsCollection_' . md5($name), $collectionContent);
		}

		$response = $this->getResponse();
		if (isset($collectionConfig['header']))
		{
			foreach ($collectionConfig['header'] as $headerName => $headerValue)
			{
				$response->setHeader($headerName, $headerValue);
			}
		}

		// TODO: implement etag, unchanged response
		//if ($collectionConfig['cache'])
		//$response->setHeader($headerName, $headerValue);
		$cacheSec = 3600;
		$response->setHeader('cache-control', 'public, max-age=' . $cacheSec . ', s-maxage=' . $cacheSec);
		$response->setHeader('expires', gmdate("D, d M Y H:i:s", time() + $cacheSec) . ' GMT');
		$response->setHeader('pragma', 'cache');

		echo $collectionContent;
	}

}
