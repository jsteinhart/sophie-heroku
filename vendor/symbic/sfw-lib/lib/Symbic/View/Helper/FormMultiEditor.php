<?php
class Symbic_View_Helper_FormMultiEditor extends Symbic_View_Helper_FormTextarea
{
	public function formMultiEditor($name, $value = null, $attribs = null, $options = array())
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable

		if (!is_array($options))
		{
			throw new Exception('Invalid options parameter');
		}

		if (empty($options['editors']))
		{
			$options['editors'] = array('codemirror');
		}
		
		if (is_string($options['editors']))
		{
			$options['editors'] = array($options['editors']);
		}

		$this->view->inlineScript()->appendFile('/components/jquery-cookie/1.4.0/jquery.cookie.js');
		
		if (!is_array($options['editors']))
		{
			throw new Exception('Invalid editors option value');
		}

		$jsOptions = array();
		$jsOptions['editors'] = $options['editors'];

		if (in_array('textarea', $options['editors']))
		{
			if (empty($options['textareaOptions']))
			{
				$options['textareaOptions'] = array();
			}
			$jsOptions['textareaOptions'] = $options['textareaOptions'];
		}
		
		if (in_array('codemirror', $options['editors']))
		{
			$this->view->headStyle()->appendFile('/components/codemirror/3.22/lib/codemirror.css');
			$this->view->inlineScript()->appendFile('/components/codemirror/3.22-build/codemirror-compressed.js');

			if (empty($options['codemirrorOptions']))
			{
				$options['codemirrorOptions'] = array();
			}
			$jsOptions['codemirrorOptions'] = $options['codemirrorOptions'];
		}

		if (in_array('tinymce', $options['editors']))
		{
			$this->view->inlineScript()->appendFile('/components/tinymce/4.0.18/js/tinymce/tinymce.min.js');

			if (empty($options['tinymceOptions']))
			{
				$options['tinymceOptions'] = array();
			}
			$jsOptions['tinymceOptions'] = $options['tinymceOptions'];
		}

		if (in_array('ckeditor', $options['editors']))
		{
			$this->view->inlineScript()->appendFile('/components/ckeditor/4.3.3/ckeditor.js');

			if (empty($options['ckeditorOptions']))
			{
				$options['ckeditorOptions'] = array();
			}
			$jsOptions['ckeditorOptions'] = $options['ckeditorOptions'];

		}
				
		$this->view->inlineScript()->appendFile('/_scripts/symbic/FormMultiEditor.js');

		$jsClass = 'SymbicFormMultiEditor';
		$jsInstance =  $jsClass . '.get(\'' . $id . '\')';
		$toolbarId = $jsClass . '_Toolbar_' . $id;

		$onLoadFunction = $jsClass . '.create(\'' . $id . '\', ' . Zend_Json::encode($jsOptions, false, array('enableJsonExprFinder' => true)) . '\');';
		$this->view->jsOnLoad()->appendScript($onLoadFunction);
		
		if (!empty($attribs['onchange']))
		{
			$this->view->jsOnLoad()->appendScript($jsInstance . '.on(\'change\', ' . $attribs['onchange'] . ');');
			unset($attribs['onchange']);
		}

		$xhtml = '';
		if (isset($attribs['toolbar']) && $attribs['toolbar'] instanceof Symbic_Toolbar_CodeMirror)
		{
			$xhtml .= '<div id="' . $toolbarId . '" class="hidden">';
			$xhtml .= $attribs['toolbar']->render($this->view, $jsInstance);
			$xhtml .= '</div>';
			unset($attribs['toolbar']);
		}

     	$xhtml .= parent::formTextarea($name, $value, $attribs);

		if (sizeof($options['editors']) > 1)
		{
			$xhtml .= '<div><div class="btn-group">';

			if (in_array('textarea', $options['editors']))
			{			
				$xhtml .= '<button type="button" class="btn btn-default btn-sm" onClick="' . $jsInstance . '.load(\'textarea\')">Textarea</button> ';
			}

			if (in_array('codemirror', $options['editors']))
			{			
				$xhtml .= '<button type="button" class="btn btn-default btn-sm" onClick="' . $jsInstance . '.load(\'codemirror\')">CodeMirror</button> ';
			}

			if (in_array('tinymce', $options['editors']))
			{			
				$xhtml .= '<button type="button" class="btn btn-default btn-sm" onClick="' . $jsInstance . '.load(\'tinymce\')">TinyMCE</button>';
			}

			if (in_array('ckeditor', $options['editors']))
			{			
				$xhtml .= '<button type="button" class="btn btn-default btn-sm" onClick="' . $jsInstance . '.load(\'ckeditor\')">CKEditor</button>';
			}

			$xhtml .= '</div></div>';
		}

        return $xhtml;
    }
}