<?php

/**
 * Class for sending an email rendered by a template.
 *
 * @category   Symbic
 * @package	   Symbic_Mail
 * @copyright  Copyright (c) 2009-2013 Symbic GmbH (http://www.symbic.de)
 */

class Symbic_Mail_Template extends Zend_Mail
{
	private $_templateEngine = null;

	private $_templateScriptText = null;
	private $_templateScriptModuleText = null;
	private $_templateScriptHtml = null;
	private $_templateScriptModuleHtml = null;

	private $_templateLayoutText = null;
	private $_templateLayoutModuleText = null;
	private $_templateLayoutHtml = null;
	private $_templateLayoutModuleHtml = null;

	private $_templateRenderedText = false;
	private $_templateRenderedHtml = false;

	public function __construct($charset = 'UTF-8')
	{
		parent::__construct($charset);
	}

	private function _initTemplateEngine()
	{
		$templateEngine = new Zend_View();
		$templateEngine->setEncoding('UTF-8');
		$templateEngine->setBasePath(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'layouts');
		$templateEngine->addHelperPath(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'helpers', 'Zend_View_Helper');
		$this->setTemplateEngine($templateEngine);
		// implement strategy for base path setting
		// $engine->setBasePath('application/mailTemplates');
	}

	public function setTemplateEngine(Zend_View_Abstract $engine)
	{
		$this->_templateEngine = $engine;
		return $this;
	}

	public function unsetTemplateEngine()
	{
		$this->_templateEngine = null;
		return $this;
	}

	public function setTemplateBasePath($basePath)
	{
		$this->getTemplateEngine()->setBasePath($basePath);
		return $this;
	}

	public function getTemplateEngine()
	{
		if ($this->_templateEngine === null)
		{
			$this->_initTemplateEngine();
		}
		return $this->_templateEngine;
	}

	public function getTemplateData()
	{
		return $this->getTemplateEngine()->getVars();
	}

	public function assignTemplateData($spec, $value = null)
	{
		$this->getTemplateEngine()->assign($spec, $value);
		return $this;
	}

	public function clearTemplateData()
	{
		$this->getTemplateEngine()->clearVars();
		return $this;
	}

	private function _addTemplateModulePath($module = null)
	{
		if ((null !== $module) && is_string($module))
		{
			$moduleDir = Zend_Controller_Front :: getInstance()->getControllerDirectory($module);
			if (null === $moduleDir)
			{
				throw new Exception('Cannot render mail template; module ' . $module . ' does not exist');
			}
			$viewsDir = dirname($moduleDir) . '/views';
			$this->_templateEngine->addBasePath($viewsDir);
		}
	}

	/* Text part template functions */
	public function getTemplateScriptText()
	{
		return $this->_templateScriptText;
	}

	public function clearTemplateScriptText()
	{
		return $this->setTemplateScriptText(null);
	}

	public function setTemplateScriptText($templateScript, $scriptModule = null)
	{
		if ($scriptModule !== null)
			$this->setTemplateScriptModuleText($scriptModule);

		$this->_templateScriptText = $templateScript;
		$this->_templateRenderedText = false;
		return $this;
	}

	public function getTemplateScriptModuleText()
	{
		return $this->_templateScriptModuleText;
	}

	public function setTemplateScriptModuleText($scriptModule)
	{
		$this->_templateScriptModuleText = $scriptModule;
		$this->_templateRenderedText = false;
		return $this;
	}

	public function clearTemplateScriptModuleText()
	{
		$this->setTemplateScriptModuleText(null);
		return $this;
	}

	private function _renderTemplateText($script = null, $scriptModule = null)
	{
		if ($script !== null)
		{
			$this->setTemplateScriptText($script, $scriptModule);
		}

		$scriptModule = $this->getTemplateScriptModuleText();
		if ($scriptModule !== null)
			$this->_addTemplateModulePath($scriptModule);

		$script = $this->getTemplateScriptText();
		$body = '';
		if ($script !== null)
		{
			$body = $this->getTemplateEngine()->render($script, $this->getCharset());
		}

		$layout = $this->getTemplateLayoutText();
		if ($layout !== null)
		{
			$layoutEngine = clone $this->getTemplateEngine();
			$layoutEngine->body = $body;
			$body = $layoutEngine->render($layout, $this->getCharset());
		}

		$this->setBodyText($body);
		$this->_templateRenderedText = true;
		return $this;
	}

	public function setBodyTextFromHtml($baseUrl)
	{
		// render HTML to get the basis which to convert to text
		$scriptModule = $this->getTemplateScriptModuleHtml();
		if ($scriptModule !== null)
		{
			$this->_addTemplateModulePath($scriptModule);
		}
		$script = $this->getTemplateScriptHtml();
		$body = '';
		if ($script !== null)
		{
			$body = $this->getTemplateEngine()->render($script, $this->getCharset());
		}

		require_once ('Html2text.php');
		$h2t = new Html2text($body);
		$h2t->set_base_url($baseUrl);
		$this->setBodyText($h2t->get_text());
	}

	/* Text part layout functions */
	public function getTemplateLayoutText()
	{
		return $this->_templateLayoutText;
	}

	public function clearTemplateLayoutText()
	{
		return $this->setTemplateLayoutText(null);
	}

	public function setTemplateLayoutText($layoutScript, $layoutModule = null)
	{
		if ($layoutModule !== null)
			$this->setTemplateLayoutModuleText($layoutModule);

		$this->_templateLayoutText = $layoutScript;
		$this->_templateRenderedText = false;
		return $this;
	}

	public function getTemplateLayoutModuleText()
	{
		return $this->_templateLayoutModuleText;
	}

	public function setTemplateLayoutModuleText($layoutModule)
	{
		$this->_templateLayoutModuleText = $layoutModule;
		$this->_templateRenderedText = false;
		return $this;
	}

	public function clearTemplateLayoutModuleText()
	{
		$this->setTemplateLayoutModuleText(null);
		return $this;
	}

	/* HTML part template functions */
	public function getTemplateScriptHtml()
	{
		return $this->_templateScriptHtml;
	}

	public function clearTemplateScriptHtml()
	{
		return $this->setTemplateScriptHtml(null);
	}

	public function setTemplateScriptHtml($templateScript, $scriptModule = null)
	{
		if ($scriptModule !== null)
			$this->setTemplateScriptModuleHtml($scriptModule);

		$this->_templateScriptHtml = $templateScript;
		$this->_templateRenderedHtml = false;
		return $this;
	}

	public function getTemplateScriptModuleHtml()
	{
		return $this->_templateScriptModuleHtml;
	}

	public function setTemplateScriptModuleHtml($scriptModule)
	{
		$this->_templateScriptModuleHtml = $scriptModule;
		$this->_templateRenderedHtml = false;
		return $this;
	}

	public function clearTemplateScriptModuleHtml()
	{
		$this->setTemplateScriptModuleHtml(null);
		return $this;
	}

	private function _renderTemplateHtml($script = null, $scriptModule = null)
	{
		if ($script !== null)
		{
			$this->setTemplateScriptHtml($script, $scriptModule);
		}

		$scriptModule = $this->getTemplateScriptModuleHtml();
		if ($scriptModule !== null)
			$this->_addTemplateModulePath($scriptModule);

		$script = $this->getTemplateScriptHtml();
		$body = '';
		if ($script !== null)
		{
			$body = $this->getTemplateEngine()->render($script, $this->getCharset());
		}

		$layout = $this->getTemplateLayoutHtml();
		if ($layout !== null)
		{
			$layoutEngine = clone $this->getTemplateEngine();
			$layoutEngine->body = $body;
			$body = $layoutEngine->render($layout, $this->getCharset());
		}

		$this->setBodyHtml($body);
		$this->_templateRenderedHtml = true;
		return $this;
	}

	/* Text part layout functions */
	public function getTemplateLayoutHtml()
	{
		return $this->_templateLayoutHtml;
	}

	public function clearTemplateLayoutHtml()
	{
		return $this->setTemplateLayoutHtml(null);
	}

	public function setTemplateLayoutHtml($layoutScript, $layoutModule = null)
	{
		if ($layoutModule !== null)
			$this->setTemplateLayoutModuleHtml($layoutModule);

		$this->_templateLayoutHtml = $layoutScript;
		$this->_templateRenderedHtml = false;
		return $this;
	}

	public function getTemplateLayoutModuleHtml()
	{
		return $this->_templateLayoutModuleHtml;
	}

	public function setTemplateLayoutModuleHtml($layoutModule)
	{
		$this->_templateLayoutModuleHtml = $layoutModule;
		$this->_templateRenderedHtml = false;
		return $this;
	}

	public function clearTemplateLayoutModuleHtml()
	{
		$this->setTemplateLayoutModuleHtml(null);
		return $this;
	}

	// main send script

	public function setBodyHtml($html, $charset = 'utf8', $encoding = null)
	{
		parent :: setBodyHtml($html, $charset, $encoding);
	}

	public function setBodyText($txt, $charset = 'utf8', $encoding = null)
	{
		parent :: setBodyText($txt, $charset, $encoding);
	}

	public function send($transport = null)
	{

		$this->setHeaderEncoding(Zend_Mime :: ENCODING_BASE64);
		if ($this->getTemplateScriptText() !== null && $this->_templateRenderedText !== true)
		{
			$this->_renderTemplateText();
		}

		if ($this->getTemplateScriptHtml() !== null && $this->_templateRenderedHtml !== true)
		{
			$this->_renderTemplateHtml();
		}

		$this->unsetTemplateEngine();

		return parent :: send($transport);
	}

}