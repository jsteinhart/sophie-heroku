<?php
namespace Application\View\Helper
{
	class ApplicationNavigation extends \Zend_View_Helper_Abstract
	{
		public function applicationNavigation()
		{
			$view = $this->view;
			$sessionUser = \Symbic_User_Session::getInstance();
			
			$entries = array();
			
			if ($sessionUser->isLoggedIn())
			{
				$entries[] = '<a href="' . $view->url(array('module'=>'expdesigner'), 'default', true) . '"><img src="/_media/Icons/application_double.png" title="Designer"> Designer</a>';
				$entries[] = '<a href="' . $view->url(array('module'=>'expadmin'), 'default', true) . '"><img src="/_media/Icons/book.png" title="Session Administration"> Session Administration</a>';

				$subMenu = array();

				$subMenu[] = '<a href="' . $view->url(array('module'=>'sfwuserprofile'), 'default', true) . '"><img src="/_media/Icons/user_edit.png" alt="Profile" title="Profile"> Profile</a>';

				if ($sessionUser->hasRight('admin'))
				{
					$subMenu[] = '<a href="' . $view->url(array('module'=>'sfwsysadmin'), 'default', true) . '"><img src="/_media/Icons/computer.png" title="Administration"> Administration</a>';
				}

				$subMenu[] = '<a href="' . $view->url(array(), 'logout', true) . '"><img src="/_media/Icons/door_in.png" title="Logout"> Logout</a>';

				$userData = $sessionUser->getData();
				if (empty($userData['name']))
				{
					$userName = $sessionUser->getLogin();
				}
				else
				{
					$userName = $userData['name'];
				}
				$subMenuLink = '<a '
					. 'href="' . $view->url(array('module'=>'sfwuserprofile'), 'default', true) . '" '
					. 'onclick="dojo.toggleClass(\'submenu_user\', \'open-submenu\');return false" ' 
					. '><img src="/_media/Icons/user.png" title="' . $userName . '"> ' . $userName . '</a>';
				$entries[] = '<div id="submenu_user" class="submenu">' . $subMenuLink . '<ul><li>' . implode('</li><li>' , $subMenu) . '</li></ul></div>';
			}
			else
			{
				$entries[] = '<a href="' . $view->url(array('module'=>'expfront'), 'default', true) . '"><img src="/_media/Icons/key.png" title="Login"> Login to Experiment</a>';
			}
			
			return '<ul><li>' . implode('</li><li>' , $entries) . '</li></ul>';
		}
	}
}