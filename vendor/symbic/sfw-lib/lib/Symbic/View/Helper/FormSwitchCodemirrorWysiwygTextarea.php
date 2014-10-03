<?php
class Symbic_View_Helper_FormSwitchCodemirrorWysiwygTextarea extends Symbic_View_Helper_FormTextarea
{
	static public $componentUrlCodemirrorCss = '/components/codemirror/3.22/lib/codemirror.css';
	static public $componentUrlCodemirrorJs = '/components/codemirror/3.22-build/codemirror-compressed.js';
	static public $componentUrlCkeditorJs = '/components/ckeditor/4.3.3/ckeditor.js';
	static public $componentUrlJqueryCookieJs = '/components/jquery-cookie/1.4.0/jquery.cookie.js';

	static public $sfwUrlFormCodemirrorTextareaJs = '/_scripts/symbic/FormCodemirrorTextarea.js';
	static public $sfwUrlFormSwitchCodemirrorWysiwygTextareaJs = '/_scripts/symbic/FormSwitchCodemirrorWysiwygTextarea.js';

	public function formSwitchCodemirrorWysiwygTextarea($name, $value = null, $attribs = null, $codemirrorOptions = array())
	{
		$info = $this->_getInfo($name, $value, $attribs);
		extract($info); // name, value, attribs, options, listsep, disable

		$this->view->headStyle()->appendFile(self :: $componentUrlCodemirrorCss);
		$this->view->inlineScript()->appendFile(self :: $componentUrlCodemirrorJs);
		//$this->view->inlineScript()->appendFile('/components/tinymce/4.0.18/js/tinymce/tinymce.min.js');
		$this->view->inlineScript()->appendFile(self :: $componentUrlCkeditorJs);
		$this->view->inlineScript()->appendFile(self :: $componentUrlJqueryCookieJs);
		$this->view->inlineScript()->appendFile(self :: $sfwUrlFormCodemirrorTextareaJs);
		$this->view->inlineScript()->appendFile(self :: $sfwUrlFormSwitchCodemirrorWysiwygTextareaJs);

		if (!isset($attribs['codemirrorOptions']))
		{
			$codemirrorOptions = array();
		}
		else
		{
			$codemirrorOptions = (array)$attribs['codemirrorOptions'];
			unset($attribs['codemirrorOptions']);
		}

		$jsInstance = 'SymbicFormSwitchCodemirrorWysiwygTextarea.get(\'' . $id . '\')';
		$elementId = $id;
		$toolbarId = 'codemirrorInstanceToolbar_' . $id;

		$appendJs = '';

		if (!empty($attribs['onchange']))
		{
			$appendJs .= $jsInstance . '.on(\'change\', function() { ' . $attribs['onchange'] . '});';
			unset($attribs['onchange']);
		}

		$onLoadFunction = 'SymbicFormSwitchCodemirrorWysiwygTextarea.create(\'' . $id . '\', {codemirrorOptions:'. Zend_Json::encode($codemirrorOptions, false, array('enableJsonExprFinder' => true)) . '});';

		$this->view->jsOnLoad()->appendScript($onLoadFunction . $appendJs);

		$xhtml = '';
		if (isset($attribs['toolbar']) && $attribs['toolbar'] instanceof Symbic_Toolbar_CodeMirror)
		{
			$xhtml .= '<div id="' . $toolbarId . '" class="hidden">';
			$xhtml .= $attribs['toolbar']->render($this->view, $jsInstance);
			$xhtml .= '</div>';

			// if the toolbar attribute still exists, it would be rendered by the formTextara -- b/c objects cannot be printed there would be an error
			unset($attribs['toolbar']);
		}
		$xhtml .= parent::formTextarea($name, $value, $attribs);

		$xhtml .= '<div><div class="btn-group">';
		$xhtml .= '<button type="button" class="btn btn-default btn-sm" onClick="' . $jsInstance . '.load(\'codemirror\')">CodeMirror</button> ';
		//$xhtml .= '<button type="button" class="btn btn-default btn-sm" onClick="' . $jsInstance . '.load(\'tinymce\')">TinyMCE</button> ';
		$xhtml .= '<button type="button" class="btn btn-default btn-sm" onClick="' . $jsInstance . '.load(\'ckeditor\')">CKEditor</button></div>';
		$xhtml .= '</div>';

		return $xhtml;
	}
}