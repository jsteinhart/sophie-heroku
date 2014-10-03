<?php
class Symbic_View_Helper_FormCodemirrorTextarea extends Symbic_View_Helper_FormTextarea
{
    public function formCodemirrorTextarea($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable
		
  		$this->view->headStyle()->appendFile('/components/codemirror/3.22/lib/codemirror.css');
 	    $this->view->inlineScript()->appendFile('/components/codemirror/3.22-build/codemirror-compressed.js');		
 	    $this->view->inlineScript()->appendFile('/_scripts/symbic/FormCodemirrorTextarea.js');

		if (!isset($attribs['codemirrorOptions']))
		{
			$codemirrorOptions = array();
		}
		else
		{
			$codemirrorOptions = (array)$attribs['codemirrorOptions'];
			unset($attribs['codemirrorOptions']);
		}

		$jsInstance = 'SymbicFormCodemirrorTextarea.get(\'' . $id . '\')';
		$elementId = $id;
		$toolbarId = 'codemirrorInstanceToolbar_' . $id;

		$appendJs = '';

		if (!empty($attribs['onchange']))
		{
			$appendJs .= $jsInstance . '.on(\'change\', function () { ' . $attribs['onchange'] . ' });';
			unset($attribs['onchange']);
		}
		
		$onLoadFunction = 'SymbicFormCodemirrorTextarea.create(\'' . $elementId . '\', {codemirrorOptions:'. Zend_Json::encode($codemirrorOptions, false, array('enableJsonExprFinder' => true)) . '});';
		
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
        return $xhtml;
    }
}