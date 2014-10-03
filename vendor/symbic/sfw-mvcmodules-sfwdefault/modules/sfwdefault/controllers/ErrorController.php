<?php

class Sfwdefault_ErrorController extends Symbic_Controller_Action
{

	protected $xAcceptHeaderKey	 = 'X-Accept';
	protected $xAcceptTypeDefault	 = 'html';
	protected $xAcceptTypeDefaultXHR = 'json';
	protected $xAcceptOptions	 = array(
		'text/html'		 => 'html',
		'text/xml'		 => 'xml',
		'text/json'		 => 'json',
		'application/json'	 => 'json',
		'application/xml'	 => 'xml'
	);

	protected function getXAcceptType()
	{
		$xAccept = $this->getRequest()->getHeader($this->xAcceptHeaderKey, null);
		if (!is_null($xAccept) && isset($this->xAcceptOptions[$xAccept]))
		{
			$xAcceptType = $this->xAcceptOptions[$xAccept];
		}
		else
		{
			if ($this->getRequest()->isXmlHttpRequest())
			{
				$xAcceptType = $this->xAcceptTypeDefaultXHR;
			}
			else
			{
				$xAcceptType = $this->xAcceptTypeDefault;
			}
		}
		return $xAcceptType;
	}

	public function errorAction()
	{
		$errorHandlerError = $this->getParam('error_handler', null);

		if (is_null($errorHandlerError) || !$errorHandlerError instanceof ArrayObject)
		{
			$this->_error('Application Error Handler called without an Error');
			return;
		}

		if (!isset($errorHandlerError->type))
		{
			$this->_error('Application Error Handler called without Error Type');
			return;
		}

		// 404 error -- controller or action not found
		if (isset($errorHandlerError->type) && $errorHandlerError->type == Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER || $errorHandlerError->type == Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION)
		{
			$this->_forward('notfound');
			return;
		}

		if (!isset($errorHandlerError->exception))
		{
			$this->_error('Application Error Handler called without an Exception');
			return;
		}

		$exception = $errorHandlerError->exception;

		$moduleConfig = $this->getModuleConfig();
		if (!isset($moduleConfig['errorException']))
		{
			$this->_error('Application Error Handler exception configuration missing');
			return;
		}

		$typeConfig = $moduleConfig['errorException'];

		if (isset($typeConfig['log']))
		{
			$logConfig	 = $typeConfig['log'];
			$loggingActive	 = (isset($logConfig['active']) && $logConfig['active'] == '1');
		}
		else
		{
			$loggingActive = false;
		}

		$loggingError		 = null;
		$loggingReferenceId	 = null;

		if ($loggingActive)
		{
			if (empty($logConfig['model']))
			{
				$loggingError = 'missingModel';
			}

			if ($loggingError === null)
			{
				try
				{
					$logModel = new $logConfig['model']();
				}
				catch (\Exception $e)
				{
					$loggingError = 'modelCreationException';
				}
			}

			if ($loggingError === null)
			{
				try
				{
					$loggingReferenceId = $logModel->log($exception);
				}
				catch (Exception $e)
				{
					$loggingError = 'modelException ' . $e->getMessage();
				}
			}
		}

		$xAcceptType = $this->getXAcceptType();

		if (isset($typeConfig['display']) && is_array($typeConfig['display']))
		{
			$displayConfig = $typeConfig['display'];
		}
		else
		{
			$displayConfig = array();
		}

		$displayActive = isset($displayConfig['active']) && $displayConfig['active'] == '1';

		if ($xAcceptType != 'html')
		{
			$response		 = array();
			$response['error']	 = 'exception';
			$response['type']	 = 'exception';

			if ($displayActive)
			{
				if (isset($displayConfig['exception']) && $displayConfig['exception'] == 1)
				{
					$response['exceptionMessage']	 = $exception->getMessage();
					$response['exceptionCode']	 = $exception->getCode();

					if (isset($displayConfig['exceptionCodeReference']) && $displayConfig['exceptionCodeReference'] == 1)
					{
						// TODO: strip BASE_PATH from file paths
						$response['exceptionFile']	 = $exception->getFile();
						$response['exceptionLine']	 = $exception->getLine();
					}

					if (isset($displayConfig['exceptionStrackTrace']) && $displayConfig['exceptionStrackTrace'] == 1)
					{
						// TODO: use extended trace using xdebug
						// TODO: strip BASE_PATH from file paths
						$response['exceptionStackTrace'] = $exception->getTrace();
					}

					if (isset($displayConfig['exceptionPrevious']) && $displayConfig['exceptionPrevious'] == 1)
					{
						$previousException = $exception->getPrevious();
						if ($previousException instanceof Exception)
						{
							$response['previousExceptionMessage']	 = $previousException->getMessage();
							$response['previousExceptionCode']	 = $previousException->getCode();
							if (isset($displayConfig['exceptionCodeReference']) && $displayConfig['exceptionCodeReference'] == 1)
							{
								$response['previousExceptionFile']	 = $previousException->getFile();
								$response['previousExceptionLine']	 = $previousException->getLine();
							}
							if (isset($displayConfig['exceptionStrackTrace']) && $displayConfig['exceptionStrackTrace'] == 1)
							{
								// TODO: use extended trace using xdebug
								// TODO: strip BASE_PATH from file paths
								$response['previousExceptionStackTrace'] = $previousException->getTrace();
							}
						}
					}
				}

				if (isset($displayConfig['requestParameters']) && $displayConfig['requestParameters'] == 1)
				{
					$response['requestParameters'] = $errorHandlerError->request->getParams();
				}
				if (isset($displayConfig['loggingStatus']) && $displayConfig['loggingStatus'] == 1)
				{
					$response['loggingActive']	 = $loggingActive;
					$response['loggingError']	 = $loggingError;
				}
			}

			if ($xAcceptType == 'json')
			{
				$this->getResponse()->setHeader('Content-type', 'application/json');
				echo Zend_Json::encode($response);
			}

			if ($xAcceptType == 'xml')
			{
				$this->getResponse()->setHeader('Content-type', 'application/xml');
				$XmlConstruct = new XmlConstruct('response');
				$XmlConstruct->fromArray($response);
				$XmlConstruct->output();
			}

			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
			return;
		}
		
		$this->view->clearVars();
		$this->view->exceptionConfig	 = $typeConfig;
		$this->view->errorHandlerError	 = $errorHandlerError;
		$this->view->translator		 = $this->getModule()->getTranslator();
		$this->view->loggingActive	 = $loggingActive;
		$this->view->loggingReferenceId	 = $loggingReferenceId;
		$this->view->loggingError	 = $loggingError;

		if (!empty($displayConfig['viewScript']))
		{
			if (file_exists($displayConfig['viewScript']))
			{
				try
				{
					$view = clone($this->view);
					$view->setScriptPath(dirname($displayConfig['viewScript']));
					echo $view->render(basename($displayConfig['viewScript']));
					$this->_helper->viewRenderer->setNoRender();
				}
				catch (Zend_View_Exception $e)
				{
					die('Rendering individual error template failed');
				}
			}
		}

		$this->_helper->layout->enableLayout();		
		if (!empty($displayConfig['layout']))
		{
			$this->_helper->layout->setLayout($displayConfig['layout']);
		}
		else
		{
			$this->_helper->layout->setLayout('default');
		}
	}

	public function notfoundAction()
	{
		$errorHandlerError = $this->getParam('error_handler', null);

		$moduleConfig = $this->getModuleConfig();
		if (!isset($moduleConfig['errorNotfound']))
		{
			$this->_error('Application Error Handler notfound configuration missing');
			return;
		}

		// TODO: implement logging

		$response = array(
			'error'	 => 'not found',
			'type'	 => 'notFound',
		);

		$typeConfig = $moduleConfig['errorNotfound'];
		if (isset($typeConfig['display']) && isset($typeConfig['display']['requestParameters']) && $typeConfig['display']['requestParameters'] == 1)
		{
			if (!is_null($errorHandlerError) && $errorHandlerError instanceof ArrayObject && isset($errorHandlerError->request))
			{
				$response['requestParameters'] = $errorHandlerError->request->getParams();
			}
		}

		$this->getResponse()->setHttpResponseCode(404);

		$xAcceptType = $this->getXAcceptType();

		if (isset($typeConfig['display']) && is_array($typeConfig['display']))
		{
			$displayConfig = $typeConfig['display'];
		}
		else
		{
			$displayConfig = array();
		}

		$displayActive = isset($displayConfig['active']) && $displayConfig['active'] == '1';

		if ($xAcceptType != 'html')
		{
			if ($xAcceptType == 'json')
			{
				$this->getResponse()->setHeader('Content-type', 'application/json');
				echo Zend_Json::encode($response);
			}

			if ($xAcceptType == 'xml')
			{
				$this->getResponse()->setHeader('Content-type', 'application/xml');
				$XmlConstruct = new XmlConstruct('response');
				$XmlConstruct->fromArray($response);
				$XmlConstruct->output();
			}
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
			return;
		}

		$this->view->clearVars();
		$this->view->moduleConfig	 = $moduleConfig;
		$this->view->translator		 = $this->getModule()->getTranslator();
		$this->view->response		 = $response;

		if (!empty($displayConfig['viewScript']))
		{
			if (file_exists($displayConfig['viewScript']))
			{
				try
				{
					$view = clone($this->view);
					$view->setScriptPath(dirname($displayConfig['viewScript']));
					echo $view->render(basename($displayConfig['viewScript']));
					$this->_helper->viewRenderer->setNoRender();
				}
				catch (Zend_View_Exception $e)
				{
					die('Rendering individual error template failed');
				}
			}
		}
	}

	public function messageAction()
	{
		$moduleConfig = $this->getModuleConfig();
		if (!isset($moduleConfig['errorMessage']))
		{
			$typeConfig = array();
		}
		else
		{
			$typeConfig = $moduleConfig['errorMessage'];
		}

		// TODO: implement logging

		$xAcceptType = $this->getXAcceptType();

		if (isset($typeConfig['display']) && is_array($typeConfig['display']))
		{
			$displayConfig = $typeConfig['display'];
		}
		else
		{
			$displayConfig = array();
		}

		$displayActive = isset($displayConfig['active']) && $displayConfig['active'] == '1';

		$type = $this->getParam('type', 'error');
		if (!in_array($type, array(
				'fatal',
				'error',
				'warning',
				'notice')))
		{
			$type = 'error';
		}

		// TODO: use debug_backtrace() to add trace information
		$response = array(
			'type'		 => $type,
			'message'	 => $this->getParam('message', ''),
			'title'		 => $this->getParam('title', '')
		);

		if ($xAcceptType != 'html')
		{
			if ($xAcceptType == 'json')
			{
				$this->getResponse()->setHeader('Content-type', 'application/json');
				echo Zend_Json::encode($response);
			}
			elseif ($xAcceptType == 'xml')
			{
				$this->getResponse()->setHeader('Content-type', 'application/xml');
				$XmlConstruct = new XmlConstruct('response');
				$XmlConstruct->fromArray($response);
				$XmlConstruct->output();
			}
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
			return;
		}

		$this->view->clearVars();
		$this->view->moduleConfig	 = $moduleConfig;
		$this->view->typeConfig		 = $typeConfig;
		$this->view->translator		 = $this->getModule()->getTranslator();
		$this->view->response		 = $response;

		if (!empty($displayConfig['viewScript']))
		{
			if (file_exists($displayConfig['viewScript']))
			{
				try
				{
					$view = clone($this->view);
					$view->setScriptPath(dirname($displayConfig['viewScript']));
					echo $view->render(basename($displayConfig['viewScript']));
					$this->_helper->viewRenderer->setNoRender();
				}
				catch (Zend_View_Exception $e)
				{
					die('Rendering individual error template failed');
				}
			}
		}
	}

}
