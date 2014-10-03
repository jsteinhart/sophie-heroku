<?php
namespace Sfwassets\View\Helper
{
	class CollectionUrl extends \Zend_View_Helper_Abstract
	{
		public function sfwassetsCollectionUrl($collectionName)
		{
			// TODO: get collection hash or version number
			return $this->view->url(array('name' => $collectionName), 'sfwassetsCollection', true);
		}
	}
}