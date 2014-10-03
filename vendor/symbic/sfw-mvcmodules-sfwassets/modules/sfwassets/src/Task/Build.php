<?php
namespace Sfwassets\Task;

class Build extends \Symbic_Task_AbstractTask
{
	public function run(array $parameters = array())
	{
		$moduleManager = \Zend_Registry::get('moduleManager');
		if (!$moduleManager)
		{
			throw new \Exception('Build Task requires Module Manager to be initialized correctly');
		}

		$module		 	= $moduleManager->getModule('sfwassets');
		$moduleConfig	= $module->getModuleConfig();

		if (!isset($moduleConfig['collections']) || !is_array($moduleConfig['collections']))
		{
			return;
		}

		// TODO: use dependency settings to allow including collections in other collections
		foreach ($moduleConfig['collections'] as $collectionName => $collectionConfig)
		{
			if (isset($collectionConfig['type']) && isset($moduleConfig['typeDefaults'][$collectionConfig['type']]))
			{
				$collectionConfig = array_replace_recursive($collectionConfig, $moduleConfig['typeDefaults'][$collectionConfig['type']]);
			}

			if (!isset($collectionConfig['build']) || !isset($collectionConfig['build']['active']) || !$collectionConfig['build']['active'])
			{
				continue;
			}

			$collection = new \Assetic\Asset\AssetCollection();

			if (isset($collectionConfig['assetFiles']) && is_array($collectionConfig['assetFiles']))
			{
				if (isset($collectionConfig['assetFileBasePath']))
				{
					$assetFileBasePath = $collectionConfig['assetFileBasePath'];
				}
				else
				{
					$assetFileBasePath = BASE_PATH . DIRECTORY_SEPARATOR;
				}

				foreach ($collectionConfig['assetFiles'] as $assetFile)
				{
					if (substr($assetFile, 0, 1) !== '/')
					{
						$assetFile = $assetFileBasePath . $assetFile;
					}

					if (!file_exists($assetFile))
					{
						trigger_error('Unknown asset linked ' . $assetFile);
						continue;
					}

					$assetContent = file_get_contents($assetFile);

					if (empty($assetContent))
					{
						continue;
					}

					$collection->add(new \Assetic\Asset\StringAsset($assetContent));
				}
			}

			if (isset($collectionConfig['assetUrls']) && is_array($collectionConfig['assetUrls']))
			{
				foreach ($collectionConfig['assetUrls'] as $assetUrl)
				{
					try
					{
						$assetContent = file_get_contents($assetUrl);
					}
					catch (Exception $e)
					{
						trigger_error('Unknown asset linked ' . $assetUrl);
						continue;
					}

					if (empty($assetContent))
					{
						continue;
					}

					$collection->add(new \Assetic\Asset\StringAsset($assetContent));
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

			if (!isset($collectionConfig['build']['filePath']))
			{
				// TODO: warn or fail
				return;
			}

			if (!is_dir($collectionConfig['build']['filePath']))
			{
				trigger_error('Asset deploy path does not exist');
				return;
			}

			if (!isset($collectionConfig['build']['fileName']))
			{
				$collectionConfig['build']['fileName'] = $collectionName;
			}

			$filename = $collectionConfig['build']['filePath'] . DIRECTORY_SEPARATOR . $collectionConfig['build']['fileName'] . '.' . $collectionConfig['build']['fileExtension'];

			file_put_contents($filename, $collectionContent);
		}
	}
}