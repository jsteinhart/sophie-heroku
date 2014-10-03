<?php
namespace Application\View\Helper
{
	class ApplicationBreadcrumbs extends \Zend_View_Helper_Abstract
	{
		public function applicationBreadcrumbs($breadcrumbs)
		{
			$content = '';
			$view = $this->view;
			
			if ( isset($breadcrumbs) && is_array($breadcrumbs) && count($breadcrumbs) > 0)
			{
				$content .= '<div id="breadcrumbs">';
				$content .= '<ul>';

				$first = true;

				foreach ($breadcrumbs as $type => $data)
				{
					$url	= '';
					$title	= '';
					$small	= '';
					$name	= '';
					$anchor	= (is_array($data) && isset($data['anchor'])) ? ('#' . $data['anchor']) : '';
					
					switch ((string)$type)
					{
						case 'home':
							$small = 'Home:';
							if ($data == 'expdesigner')
							{
								$url	= $view->url(array('module' => 'expdesigner'), 'default', true);
								$title	= 'Experiment Designer Homepage';
								$name	= 'Designer';
							}
							elseif ($data == 'expadmin')
							{
								$url	= $view->url(array('module' => 'expadmin'), 'default', true);
								$title	= 'Session Administration Homepage';
								$name	= 'Session Administration';
							}
							elseif ($data == 'sfwsysadmin')
							{
								$url	= $view->url(array('module' => 'sfwsysadmin'), 'default', true);
								$title	= 'Administration';
								$name	= 'Administration';
							}
							break;
						case 'experiment':
							$url	= $view->url(array('module'=>'expdesigner', 'controller'=>'treatment', 'action'=>'index', 'experimentId'=>$data['id']), 'default', true);
							$title	= 'Experiment overview: Treatments';
							$small	= 'Experiment:';
							$name	= $data['name'];
							break;
						case 'treatment':
							$url	= $view->url(array('module'=>'expdesigner', 'controller'=>'treatment', 'action'=>'details', 'treatmentId'=>$data['id']), 'default', true);
							$title	= 'Treatment overview: Structure and Participants';
							$small	= 'Treatment:';
							$name	= $data['name'];
							break;
						case 'stepgroup':
							$url	= $view->url(array('module'=>'expdesigner', 'controller'=>'treatment', 'action'=>'details', 'treatmentId'=>$data['treatmentId']), 'default', true);
							$title	= 'Edit stepgroup';
							$small	= 'Stepgroup:';
							$name	= $data['name'];
							$anchor = '#stepgroup' . $data['id'];
							break;
						case 'step':
							$url	= $view->url(array('module'=>'expdesigner', 'controller'=>'step', 'action'=>'index', 'stepId'=>$data['id']), 'default', true);
							$title	= 'Edit step';
							$small	= 'Step:';
							$name	= $data['name'];
							break;
						case 'type':
							$url	= $view->url(array('module'=>'expdesigner', 'controller'=>'type', 'action'=>'edit', 'typeLabel'=>$data['typeLabel'], 'treatmentId'=>$data['treatmentId']), 'default', true);
							$title	= 'Edit type';
							$small	= 'Type:';
							$name	= $data['name'];
							break;
						case 'parameter':
							$url	= $view->url(array('module'=>'expdesigner', 'controller'=>'parameter', 'action'=>'edit', 'parameterName'=>$data['name'], 'treatmentId'=>$data['treatmentId']), 'default', true);
							$title	= 'Edit parameter';
							$small	= 'Parameter:';
							$name	= $data['name'];
							break;
						case 'variable':
							$url	= $view->url(array('module'=>'expdesigner', 'controller'=>'variable', 'action'=>'edit', 'variableName'=>$data['name'], 'treatmentId'=>$data['treatmentId']), 'default', true);
							$title	= 'Edit variable';
							$small	= 'Variable:';
							$name	= $data['name'];
							break;
						case 'session':
							$url	= $view->url(array('module'=>'expadmin', 'controller'=>'session', 'action'=>'details', 'sessionId'=>$data['sessionId']), 'default', true);
							$title	= 'Session Details';
							$small	= 'Session Details:';
							$name	= $data['name'];
							break;
						default:
							$url	= (isset($data['url'])) ? $data['url'] : false;
							$title	= (isset($data['title'])) ? $data['title'] : false;
							$small	= (isset($data['small'])) ? $data['small'] : false;
							$name	= (isset($data['name'])) ? $data['name'] : false;
							if (!$url && !$title && !$small && !$name)
							{
								continue 2;
							}
					}
					if (!$first)
					{
						$content .= '<li>&raquo;</li>';
					}
					$first = false;

					$content .= '<li>';
					
					if (!empty($url))
					{
						$content .= '<a href="' . $url . $anchor . '" title="' . $view->escape($title) .'">';
					}
					else
					{
						$content .= '<div title="' . $view->escape($title) . '">';
					}
					
					$content .= '<small>' . $view->escape($small) . '</small>';
					$content .= '<span>' . $view->escape($name) . '</span>';
					
					if (!empty($url))
					{
						$content .= '</a>';
					}
					else
					{
						$content .= '</div>';
					}
					$content .= '</li>';
				}

				$content .= '</ul>';
				$content .= '<div class="clear"></div>';
				$content .= '</div>';
			}		
			
			return $content;
		}
	}
}