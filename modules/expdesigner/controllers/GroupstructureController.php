<?php
class Expdesigner_GroupstructureController extends Symbic_Controller_Action
{
	private $experimentId = null;
	private $treatmentId = null;
	private $label = null;

	private $experiment = null;
	private $treatment = null;
	private $structure = null;

	public function preDispatch()
	{
		$this->treatmentId = $this->_getParam('treatmentId', null);
		if (empty ($this->treatmentId))
		{
			$this->_error('Paramater treatmentId missing!');
			return;
		}

		$this->treatment = Sophie_Db_Treatment :: getInstance()->find($this->treatmentId)->current();
		if (is_null($this->treatment))
		{
			$this->_error('Selected treatment does not exist!');
			return;
		}
		$this->experiment = $this->treatment->findParentRow('Sophie_Db_Experiment');
		$this->experimentId = $this->experiment->id;

		$acl = System_Acl :: getInstance();
		if (!$acl->autoCheckAcl('experiment',  $this->experiment->id, 'sophie_experiment'))
		{
			$this->_error('Access denied');
			return;
		}
		
		$this->view->breadcrumbs = array (
			'home' => 'expdesigner',
			'experiment' => array (
				'id' => $this->experiment->id,
				'name' => $this->experiment->name
			),
			'treatment' => array (
				'id' => $this->treatment->id,
				'name' => $this->treatment->name,
				'anchor' => 'tab_treatmentParticipantTab'
			)
		);

		// IMPORTANT:
		// We chose to support only one single group structure, but create the database structure
		// sustainable and perpare multi group structure support within the database.
		// The only available group structure is labeled "G":
		$this->label = 'G';
		// $this->label = $this->_getParam('label', null);

		if (!empty($this->label))
		{
			$this->structure = Sophie_Db_Treatment_Group_Structure :: getInstance()->fetchDisassembledRow($this->treatment->id, $this->label);

			if (is_null($this->structure))
			{
				$this->_error('Selected structure does not exist or does not belong to selected treatment!');
				return;
			}
		}
	}

	public function listAction()
	{
		$this->view->groupStructure = Sophie_Db_Treatment_Group_Structure :: getInstance()->fetchDisassembledRow($this->treatment->id, 'G');

		$this->view->experiment = $this->experiment->toArray();
		$this->view->treatment = $this->treatment->toArray();

		$this->_helper->layout->disableLayout();
	}

	public function addAction()
	{
		$this->_forward('edit');
	}

	public function editAction()
	{
		if (is_null($this->structure))
		{
			$this->_error('Missing label parameter');
			return;
		}

		$form = $this->getForm('Group_Structure_Edit');
		$form->createElementsFromStructure($this->structure['structure']);
		$form->setAction($this->view->url());

		$formData = array(
			'treatmentId' => $this->treatment->id,
			'label' => $this->structure['label'],
			'name' => $this->structure['name'],
			'minmax' => array(),
			'min' => array(),
			'max' => array()
		);
		
		foreach ($this->structure['structure'] as $key => $struc)
		{
			$formData['min_' . $key] = $struc['min'];
			$formData['max_' . $key] = $struc['max'];
		}

		$form->setDefaults($formData);

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$table = Sophie_Db_Treatment_Group_Structure :: getInstance();

				$values = $form->getValues();
				$data = array(
					'name' => $values['name'],
					'structure' => array()
				);

				foreach ($this->structure['structure'] as $key => $struc)
				{
					$data['structure'][$key] = array(
						'min' => $values['min_' . $key],
						'max' => $values['min_' . $key]
					);
				}
				$where = $table->getAdapter()->quoteInto('treatmentId = ?', $this->treatmentId) . ' AND ' .$table->getAdapter()->quoteInto('label = ?', $this->label);
				$table->update($data, $where);

				$this->_helper->flashMessenger('Changes in group structure saved');
				$this->_helper->getHelper('Redirector')->setPrependBase('')->gotoUrl($this->_helper->getHelper('Url')->url(array (
					'module' => 'expdesigner',
					'controller' => 'treatment',
					'action' => 'details',
					'treatmentId' => $this->treatment->id
				)) . '#tab_treatmentParticipantTab');
				return;
			}
		}

		$this->view->breadcrumbs['__own'] = array (
			'title' => 'Edit Group Structure',
			'small' => 'Group Structure:',
			'name' => $this->structure['name']
		);

		$this->view->form = $form;
		$this->view->experiment = $this->experiment->toArray();
		$this->view->treatment = $this->treatment->toArray();
		$this->view->structure = $this->structure;

		// for warning message concerning invalidation of sessiontypes:
		$sessiontypes = $this->treatment->findDependentRowset('Sophie_Db_Treatment_Sessiontype', null, Sophie_Db_Treatment_Sessiontype :: getInstance()->select()->where('state <> "deleted"'))->toArray();
		$this->view->hasSessiontypes = (count($sessiontypes) > 0);
	}

	public function deleteAction()
	{

	}
}