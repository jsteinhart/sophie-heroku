<?php
class Expdesigner_SteptypeController extends Symbic_Controller_Action
{

	public function preDispatch()
	{
	}

	public function treeAction()
	{
		$db = Zend_Registry::get('db');
		$steptypeModel = new Sophie_Db_Steptype();

		$steptypeTree = array ();

		$steptypeCategories = $db->fetchAll('SELECT category FROM sophie_steptype WHERE isInstalled = 1 AND isAbstract = 0 AND isActive = 1 AND isBroken = 0 GROUP BY category ORDER BY category');
		foreach ($steptypeCategories as $steptypeCategory)
		{
			$categoryData = array ();
			$categoryData['id'] = 'steptypeCategory' . md5(implode('-', explode(',', $steptypeCategory['category'])));
			$categoryData['type'] = 'steptypeCategory';
			$categoryData['label'] = implode(' - ', explode(',', $steptypeCategory['category']));
			$categoryData['children'] = array ();

			$steptypes = $steptypeModel->fetchAll('category = ' . $db->quote($steptypeCategory['category']) . ' AND isInstalled = 1 AND isAbstract = 0 AND isActive = 1 AND isBroken = 0', 'name ASC');
			foreach ($steptypes as $steptype)
			{
				$nodeData = array ();
				$nodeData['id'] = 'steptype' . $steptype->systemName;
				$nodeData['label'] = $steptype->name;
				//$nodeData['label'] .= ' (' . $steptype->version . ')';

				$nodeData['type'] = 'steptype';
				$nodeData['steptypeId'] = $steptype->systemName;
				$nodeData['systemName'] = $steptype->systemName;
				$nodeData['name'] = $steptype->name;
				$nodeData['version'] = $steptype->version;
				$nodeData['isInstalled'] = $steptype->isInstalled;
				$nodeData['isAbstract'] = $steptype->isAbstract;
				$nodeData['isActive'] = $steptype->isActive;
				$nodeData['isBroken'] = $steptype->isBroken;

				$categoryData['children'][] = $nodeData;
			}

			$steptypeTree[] = $categoryData;
		}

		$data = new Zend_Dojo_Data('id', $steptypeTree);
		$data->setLabel('label');
		echo $data->toJson();

		exit;
	}

}