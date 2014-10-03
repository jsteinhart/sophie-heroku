<?php
class Symbic_Toolbar_CodeMirror
{

	public $view;
	public $codeMirrorJsObjectName;

	public function render($view, $codeMirrorJsObjectName, $options = array ())
	{
		$this->view = $view;
		$this->codeMirrorJsObjectName = $codeMirrorJsObjectName;

		$this->view->dojo()->requireModule('dijit.MenuBar');
		$this->view->dojo()->requireModule('dijit.MenuBarItem');
		$this->view->dojo()->requireModule('dijit.PopupMenuBarItem');
		$this->view->dojo()->requireModule('dijit.Menu');
		$this->view->dojo()->requireModule('dijit.MenuItem');
		$this->view->dojo()->requireModule('dijit.PopupMenuItem');

		$menuId = 'navMenu' . md5($this->codeMirrorJsObjectName);
		$cnt = 0;

		$xhtml = '<div dojoType="dijit.MenuBar" id="' . $menuId . '">';

		foreach ($options as $menuBarItem)
		{
			$xhtml .= $this->renderItem($menuBarItem, 1) . "\n";
		}

		$xhtml .= '</div>';

		return $xhtml;
	}

	public function renderItem($menuItem, $level)
	{
		$xhtml = '';

		// handle separators
		if (isset ($menuItem['type']) && $menuItem['type'] == 'separator' && $level > 1)
		{
			$xhtml .= '<div dojoType="dijit.MenuSeparator"></div>';
		}

		// handle submenu item
		elseif (isset ($menuItem['items']) && is_array($menuItem['items']))
		{
			// use menu bar item on first level
			if ($level == 1)
			{
				$menuItemType = 'dijit.PopupMenuBarItem';
			}

			// use menu item from level second upwards
			else
			{
				$menuItemType = 'dijit.PopupMenuItem';
			}

			$xhtml .= '<div dojoType="' . $menuItemType . '"';
			if (isset ($menuItem['title']) && $menuItem['title'] != '')
			{
				$xhtml .= ' title="' . $this->view->escape($menuItem['title']) . '"';
			}
			$xhtml .= '>';

			$xhtml .= '<span>';
			$xhtml .= (isset ($menuItem['htmlTitle'])) ? $menuItem['htmlTitle'] : $this->view->escape($menuItem['title']);
			$xhtml .= '</span>';

			$xhtml .= '<div dojoType="dijit.Menu"';
			// $xhtml .= ' id="' . $menuId . '_' . $cnt++ . '
			$xhtml .= '>';

			foreach ($menuItem['items'] as $subMenuItem)
			{
				$xhtml .= $this->renderItem($subMenuItem, $level +1);
			}

			$xhtml .= '</div>';

			$xhtml .= '</div>';
		}

		else
		{

			// use menu bar item on first level
			if ($level == 1)
			{
				$menuItemType = 'dijit.MenuBarItem';
			}

			// use menu item from level second upwards
			else
			{
				$menuItemType = 'dijit.MenuItem';
			}

			$xhtml .= '<div dojoType="' . $menuItemType . '"';
			if (isset ($menuItem['onclick']) && $menuItem['onclick'] != '')
			{
				if (strpos($menuItem['onclick'], '"'))
				{
					$menuItem['onclick'] = 'alert(unescape(\'Please do not use %22 as string delimeter.\'))';
				}
				$xhtml .= ' onClick="' . $menuItem['onclick'] . '"';
			}
			if (isset ($menuItem['title']) && $menuItem['title'] != '')
			{
				$xhtml .= ' title="' . $this->view->escape($menuItem['title']) . '"';
			}
			$xhtml .= '>';

			$xhtml .= (isset ($menuItem['htmlTitle'])) ? $menuItem['htmlTitle'] : $this->view->escape($menuItem['title']);

			$xhtml .= '</div>';

		}

		$xhtml .= "\n";
		return $xhtml;

	}

}