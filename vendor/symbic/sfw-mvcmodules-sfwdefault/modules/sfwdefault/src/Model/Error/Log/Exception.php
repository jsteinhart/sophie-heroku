<?php

/*
  CREATE TABLE IF NOT EXISTS `system_log_error_exception` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `referenceId` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `message` longtext COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file` text COLLATE utf8_unicode_ci NOT NULL,
  `line` int(11) NOT NULL,
  `stackTrace` longtext COLLATE utf8_unicode_ci NOT NULL,
  `data` longtext COLLATE utf8_unicode_ci NOT NULL,
  `previousType` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `previousType2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `previousMessage` longtext COLLATE utf8_unicode_ci NOT NULL,
  `previousCode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `previousFile` text COLLATE utf8_unicode_ci NOT NULL,
  `previousLine` int(11) NOT NULL,
  `previousStackTrace` longtext COLLATE utf8_unicode_ci NOT NULL,
  `requestModule` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `requestController` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `requestAction` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `requestParameters` longtext COLLATE utf8_unicode_ci NOT NULL,
  `sessionId` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `userId` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `userLogin` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `referenceId` (`referenceId`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */
 
namespace Sfwdefault\Model\Error\Log;
 
class Exception extends AbstractLog
{

	protected $logTableName = 'system_log_error_exception';

	public function log(\Exception $exception, $loggingReferenceId = null)
	{
		$logData = array();

		if (is_null($loggingReferenceId))
		{
			// TODO: implement a host prefix to prevent duplicate referenceId on multiple hosts
			$hostPrefix		 = '1-';
			$loggingReferenceId	 = str_replace('.', '-', uniqid($hostPrefix, true));
		}

		$logData['referenceId']	 = $loggingReferenceId;
		$logData['type']	 = 'exception';
		$logData['type2']	 = get_class($exception);
		$logData['message']	 = $exception->getMessage();
		$logData['code']	 = $exception->getCode();
		$logData['file']	 = $this->normalizePath($exception->getFile());
		$logData['line']	 = $exception->getLine();

		$stackTrace = $exception->getTrace();

		foreach ($stackTrace as &$stackTraceItem)
		{
			if (!isset($stackTraceItem['file']))
			{
				continue;
			}
			$stackTraceItem['file'] = $this->normalizePath($stackTraceItem['file']);
		}
		$logData['stackTrace'] = \Zend_Json::encode($stackTrace);

		$previousException = $exception->getPrevious();
		if ($previousException != null)
		{
			$logData['previousType']	 = 'exception';
			$logData['previousType2']	 = get_class($previousException);
			$logData['previousMessage']	 = $previousException->getMessage();
			$logData['previousCode']	 = $previousException->getCode();
			$logData['previousFile']	 = $this->normalizePath($previousException->getFile());
			$logData['previousLine']	 = $previousException->getLine();

			$stackTrace = $previousException->getTrace();
			foreach ($stackTrace as &$stackTraceItem)
			{
				if (!isset($stackTraceItem['file']))
				{
					continue;
				}
				$stackTraceItem['file'] = $this->normalizePath($stackTraceItem['file']);
			}
			$logData['previousStackTrace'] = \Zend_Json::encode($stackTrace);
		}

		if (isset($errorHandlerError->request))
		{
			$request			 = $errorHandlerError->request;
			$logData['requestModule']	 = $request->getModuleName();
			$logData['requestController']	 = $request->getControllerName();
			$logData['requestAction']	 = $request->getActionName();

			if (isset($logConfig['requestParameters']) && $logConfig['requestParameters'] == 1)
			{
				$requestParameters = $request->getParams();

				$moduleKey = $request->getModuleKey();
				if (isset($requestParameters[$moduleKey]))
				{
					unset($requestParameters[$moduleKey]);
				}
				$controllerKey = $request->getControllerKey();
				if (isset($requestParameters[$controllerKey]))
				{
					unset($requestParameters[$controllerKey]);
				}
				$actionKey = $request->getActionKey();
				if (isset($requestParameters[$actionKey]))
				{
					unset($requestParameters[$actionKey]);
				}

				$logData['requestParameters'] = Zend_Json::encode($requestParameters);
			}
		}

		if (\Zend_Session::sessionExists())
		{
			$logConfig['sessionId'] = \Zend_Session::getId();
			if (isset($logConfig['userSession']) && $logConfig['userSession'] == 1)
			{
				$userSession = \Symbic_User_Session::getInstance();
				if ($userSession->isLoggedIn())
				{
					$logData['userId']	 = $userSession->getId();
					$logData['userLogin']	 = $userSession->getLogin();
				}
			}
		}

		return $loggingReferenceId;
	}
}