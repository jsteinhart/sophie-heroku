<?php
class Symbic_Application_Resource_View extends Zend_Application_Resource_View
{
	public $_explicitType = 'view';
	
    public function getView()
    {
        if ($this->_view === null)
		{
            $options = $this->getOptions();
			if (isset($options['class']))
			{
				$viewClass = $options['class'];
			}
			else
			{
				$viewClass = 'Symbic_View_Standard';
			}
			
            $this->_view = new $viewClass($options);

            if (isset($options['doctype']))
			{
                $this->_view->doctype()->setDoctype(strtoupper($options['doctype']));
                if (isset($options['charset']) && $this->_view->doctype()->isHtml5()) {
                    $this->_view->headMeta()->setCharset($options['charset']);
                }
            }
			
            if (isset($options['contentType']))
			{
                $this->_view->headMeta()->appendHttpEquiv('Content-Type', $options['contentType']);
            }
			
            if (isset($options['assign']) && is_array($options['assign']))
			{
                $this->_view->assign($options['assign']);
            }
        }
        return $this->_view;			
    }
}
