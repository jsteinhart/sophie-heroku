<?php
class Sfwdashboard_IndexController extends Symbic_Controller_Action
{
	public function indexAction()
	{
		$moduleConfig = $this->getModuleConfig();
		if (isset($moduleConfig['headline']) && $moduleConfig['headline'] != '')
		{
			$this->view->headline = $moduleConfig['headline'];
		}

		// TODO: implement a block source adapter
		if (isset($moduleConfig['blocks']) && is_array($moduleConfig['blocks']))
		{
			$blocks = $moduleConfig['blocks'];
		}
		else
		{
			$blocks = array();
		}

		foreach ($blocks as &$block)
		{
			if (!isset($block['type']) || $block['type'] == '')
			{
				continue;
			}

			// TODO: implement a block type adapters
			switch ($block['type'])
			{
				case 'html':
					break;

				case 'viewScript':
					if (!isset($block['scriptFilename']) || $block['scriptFilename'] == '')
					{
						continue;
					}
					if (!file_exists($block['scriptFilename']))
					{
						continue;
					}
					$blockView = clone($this->view);
					$blockView->setScriptPath(dirname($block['scriptFilename']));
					$block['content'] = $blockView->render(basename($block['scriptFilename']));
					break;

				case 'viewPartial':
					if (!isset($block['partialName']) || $block['partialName'] == '')
					{
						continue;
					}
					if (!isset($block['partialModule']) || $block['partialModule'] == '')
					{
						continue;
					}
					if (!isset($block['partialParams']) || !is_array($block['partialParams']))
					{
						$block['partialParams'] = array();
					}

					$block['content'] = $this->view->partial($block['partialName'], $block['partialModule'], $block['partialParams']);
					break;
					
				/*
					case '...':
						// render block into content field
						$block['content'] = '...';
						break;
				*/
					default:
						continue;
			}
		}
		$this->view->blocks = $blocks;
	}
}