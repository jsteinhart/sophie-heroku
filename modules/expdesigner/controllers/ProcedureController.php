<?php
class Expdesigner_ProcedureController extends Symbic_Controller_Action
{
	private $experimentId = null;
	private $treatmentId = null;

	private $experiment = null;
	private $treatment = null;

	public function preDispatch()
	{
		$this->treatmentId = $this->_getParam('treatmentId', null);
		if (!is_null($this->treatmentId))
		{
			// if treatmentId given: use it to get treatment and experiment
			$this->treatment = Sophie_Db_Treatment :: getInstance()->find($this->treatmentId)->current();
			if (is_null($this->treatment))
			{
				$this->_error('Selected treatment does not exist!');
				return;
			}
			$this->experiment = $this->treatment->findParentRow('Sophie_Db_Experiment');
			$this->experimentId = $this->experiment->id;

		}
		else
		{
			// otherwise get experimentId and experiment
			$this->experimentId = $this->_getParam('experimentId', null);
			$this->experiment = Sophie_Db_Experiment :: getInstance()->find($this->experimentId)->current();
			if (is_null($this->experiment))
			{
				$this->_error('Selected experiment does not exist!');
				return;
			}
		}

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


		);
		if ($this->treatment)
		{
			$this->view->breadcrumbs['treatment'] = array (
				'id' => $this->treatment->id,
				'name' => $this->treatment->name
			);
		}
	}

	public function graphAction()
	{
		$treatmentId = $this->_getParam('treatmentId', 0);
		if (is_null($treatmentId) || $treatmentId == 0)
		{
			$this->_error('Missing parameter');
			return;
		}

		$this->treatment = Sophie_Db_Treatment :: getInstance()->find($treatmentId)->current();
		if (is_null($this->treatment))
		{
			$this->_error('Selected treatment does not exist or does not belong to selected experiment!');
			return;
		}

		$format = $this->_getParam('format', 'svg');
		if (!in_array($format, array('gif', 'dot', 'svg', 'pdf')))
		{
			$format = 'svg';
		}

		$direction = $this->_getParam('direction', 'TB');
		if (!in_array($direction, array('LR', 'TB')))
		{
			$direction = 'TB';
		}

		$graph = new Symbic_Image_GraphViz(
				true,
				array('rankdir'=>$direction, 'compound' => 'true')
			);

		$this->stepgroups = $this->treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup', null, Sophie_Db_Treatment_Stepgroup :: getInstance()->select()->order('position'));
		$this->types = $this->treatment->findDependentRowset('Sophie_Db_Treatment_Type', null, Sophie_Db_Treatment_Type :: getInstance()->select()->order('label'));

		if ($this->types > 1)
		{
			// TODO: look for runconditions
			// TODO: add swimlanes if we have runconditions
		}

		$this->steps = array ();

		$stepDbModel = Sophie_Db_Treatment_Step :: getInstance();

		$graphLastStepgroupId = null;
		$graphStepgroupFirstStepId = null;
		$graphLastStepId = null;

		foreach ($this->stepgroups as $stepgroup)
		{

			$graphStepgroupId = 'stepgroup_' . $stepgroup->id;

			$graphStepgroupAttribs = array();
			//$graphStepgroupAttribs['shape'] = 'box';
			//$graphStepgroupAttribs['label'] = $stepgroup->label . ': ' . $stepgroup->name;

			$graph->addCluster(
				$graphStepgroupId,
				$stepgroup->label . ': ' . $stepgroup->name
				//$graphStepgroupAttribs,
				//'default'
		 	);

/*			if (!is_null($graphLastStepgroupId))
			{
				$graph->addEdge(
					array($graphLastStepgroupId => $graphStepgroupId),
					array('label' => '')
				);
			} */

			$steps = $stepDbModel->fetchAllByStepgroupIdJoinParticipantTypesAndSteptype($stepgroup->id);
			foreach ($steps as $step)
			{
				$graphStepId = 'step_' . $step['id'];

				$graphStepAttribs = array();
				//$graphStepAttribs['shape'] = 'box';
				$graphStepAttribs['label'] = $step['id'] . ': ' . $step['name'];

				$graph->addNode(
					$graphStepId,
					$graphStepAttribs,
					$graphStepgroupId
			 	);

				if (!is_null($graphLastStepId))
				{
					$graphStepEdgeAttribs = array();
					$graphStepEdgeAttribs['label'] = '';
					if (is_null($graphStepgroupFirstStepId))
					{
						$graphStepEdgeAttribs['ltail'] = $graphLastStepgroupId;
						$graphStepEdgeAttribs['lhead'] = $graphStepgroupId;
					}
					$graph->addEdge(
						array($graphLastStepId => $graphStepId),
						$graphStepEdgeAttribs

					);
				}

				if (is_null($graphStepgroupFirstStepId))
				{
					$graphStepgroupFirstStepId = $graphStepId;
				}

				$graphLastStepId = $graphStepId;
			}

			if ($stepgroup->loop > 1)
			{
				$graph->addEdge(
						array($graphStepId => $graphStepgroupFirstStepId),
						array(
							'label' => 'Repeat Stepgroup x ' . $stepgroup->loop,
							'style' => 'dashed',
							'constraint' => 'false'
							)
					);
			}

			$graphStepgroupFirstStepId = null;
			$graphLastStepgroupId = $graphStepgroupId;
		}

		if ($format == 'dot')
		{
			echo $graph->parse();
		}
		else
		{
			//echo $subsidiaryTreeGraphViz->fetch($format);
			$graph->image($format);
		}
		exit;
	}
}