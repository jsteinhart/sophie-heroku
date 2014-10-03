<?php
class Sophie_Validate_PHPCode extends Symbic_Validate_PHPCode
{
	protected static $sophieAllowedFunctionsWhite = null;
	protected static $sophieAllowedFunctionsWhiteAndBlack = null;

	public function getAllowedFunctionsWhite()
	{
		if (self::$sophieAllowedFunctionsWhite === null)
		{
			$cacheName = 'SophieAllowedFunctionsWhite';
			$cache = Zend_Registry :: get('Zend_Cache');
			$allowedFunctions = $cache->load($cacheName);

			if (!$allowedFunctions)
			{
				//Get db connection for whitelist
				$model = new Zend_Db_Table('sophie_validate_phpcode_function');
				$where = 'allowed = 1';
				$result = $model->fetchAll($where);

				$allowedFunctions = array();
				foreach ($result as $function)
				{
					$allowedFunctions[$function->function] = $function->allowed;
				}

				$cache->save($allowedFunctions, $cacheName);
			}
			self::$sophieAllowedFunctionsWhite = $allowedFunctions;
		}
		return self::$sophieAllowedFunctionsWhite;
	}
	
	public function getAllowedFunctionsWhiteAndBlack()
	{
		if (self::$sophieAllowedFunctionsWhiteAndBlack === null)
		{
			$cacheName = 'SophieAllowedFunctionsWhiteAndBlack';
			$cache = Zend_Registry :: get('Zend_Cache');
			$allowedFunctions = $cache->load($cacheName);

			if (!$allowedFunctions)
			{
				//Get db connection for whitelist
				$model = new Zend_Db_Table('sophie_validate_phpcode_function');
				$result = $model->fetchAll();

				$allowedFunctions = array();
				foreach ($result as $function)
				{
					$allowedFunctions[$function->function] = $function->allowed;
				}

				$cache->save($allowedFunctions, $cacheName);
			}
			self::$sophieAllowedFunctionsWhiteAndBlack = $allowedFunctions;
		}
		return self::$sophieAllowedFunctionsWhiteAndBlack;
	}
}