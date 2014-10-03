<?php
class Expadmin_ParticipantController extends Symbic_Controller_Action
{
	public $participantStates = array(
		'new' => 'new',
		'started' => 'started',
		'finished' => 'finished',
		'excluded' => 'excluded'
	);

	public function preDispatch()
	{
		$sessionId = $this->_getParam('sessionId', 0);
		if ($sessionId == 0) {
			$this->_error('Missing parameter sessionId');
			return;
		}

		$this->session = Sophie_Db_Session::getInstance()->find($sessionId)->current();
		if (is_null($this->session)) {
			$this->_error('Selected session does not exist!');
			return;
		}

		$acl = System_Acl::getInstance();
		if (!$acl->autoCheckAcl('session', $this->session->id, 'sophie_session')) {
			$this->_error('Access denied.');
			return;
		}

		$popup = $this->_getParam('popup', false);
		if ($popup) {
			$this->_helper->layout->setLayout('popup');
		}
	}

	public function listAction()
	{
		$treatment = $this->session->findParentRow('Sophie_Db_Treatment');
		$this->view->treatment = $treatment->toArray();
		$this->view->session = $this->session->toArray();
		$this->view->participants = Sophie_Db_Session_Participant::getInstance()->findBySessionWithState($this->session->id);
		$this->_helper->layout->disableLayout();
	}

	public function processAction()
	{
		$treatment = $this->session->findParentRow('Sophie_Db_Treatment');

		$this->stepgroups = $this->treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup', null, Sophie_Db_Treatment_Stepgroup :: getInstance()->select()->order('position'));
		$this->view->stepgroups = $this->stepgroups->toArray();

		$this->steps = array();
		$this->view->steps = array();

		$stepDbModel = Sophie_Db_Treatment_Step :: getInstance();

		foreach ($this->stepgroups as $stepgroup) {
			// TODO: join participants to steps
			$steps = $stepDbModel->fetchAllByStepgroupIdJoinParticipantTypesAndSteptype($stepgroup->id);
			$this->steps[$stepgroup->id] = $steps;
			$this->view->steps[$stepgroup->id] = $this->steps[$stepgroup->id];
		}

		$this->view->treatment = $treatment->toArray();
		$this->view->session = $this->session->toArray();
		$this->_helper->layout->disableLayout();
	}

	public function codesAction()
	{
		$treatment = $this->session->findParentRow('Sophie_Db_Treatment');

		$codesSelect = Sophie_Db_Session_Participant :: getInstance()->select()->where('sessionId = ?', $this->session->id);

		$order = $this->_getParam('order', 'rand');
		if ($order == 'rand')
		{
			$codesSelect->order(new Zend_Db_Expr('Rand()'));
		}
		else
		{
			$codesSelect->order('code ASC');
		}

		$codes = Sophie_Db_Session_Participant :: getInstance()->fetchAll($codesSelect);

		$this->view->codes = $codes->toArray();
		$this->view->treatment = $treatment->toArray();
		$this->view->session = $this->session->toArray();

		$outputFormat = $this->_getParam('outputFormat', 'html');

		switch ($outputFormat) {
			case 'html':
				$this->_helper->layout->setLayout('popup');
				break;

			case 'pdf':
				// TODO: implement pdf list?
				$this->_error('Not yet implemented');
				return;
				$this->_helper->layout->disableLayout();
				break;

			case 'csv':
				$this->getResponse()->setHeader('Content-Type', 'text/csv');
				$this->getResponse()->setHeader('Content-disposition', 'attachment;filename=codes_session' . $this->session->id . '.csv');
				$this->_helper->layout->disableLayout();
				break;

			case 'xlsx':
				$this->getResponse()->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				$this->getResponse()->setHeader('Content-disposition', 'attachment;filename=codes_session' . $this->session->id . '.xlsx');
				$this->_helper->layout->disableLayout();
				break;

			default:
				throw new Exception('Output format for codes list is unkown: ' . $outputFormat);
		}

		$this->_helper->viewRenderer('codes-' . $outputFormat);
	}

	public function printcodesnippetsAction()
	{
		$form = $this->getForm('Participant_Codes_Printing_Snippets');

		$config = Zend_Registry::get('config');
		$printers = $config['systemConfig']['receiptPrinters'];
		$printerOptions = array();
		foreach ($printers as $printer) {
			$printerOptions[$printer['name']] = $printer['name'];
		}
		$form->setPrinterOptions($printerOptions);

		$headlineValue = $this->session->name;
		$form->setHeadlineValue($headlineValue);

		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$values = $form->getValues();

				$codesSelect = Sophie_Db_Session_Participant :: getInstance()->select()->where('sessionId = ?', $this->session->id);

				if ($values['order'] == 'rand')
				{
					$codesSelect->order(new Zend_Db_Expr('Rand()'));
				}
				else
				{
					$codesSelect->order('code ASC');
				}

				$codes = Sophie_Db_Session_Participant :: getInstance()->fetchAll($codesSelect);

				foreach ($printers as $printer) {
					if ($printer['name'] == $values['printer']) {
						break;
					}
				}

				$this->view->values = $values;
				$this->view->printer = $printer;

				if ($printer['type'] == 'epson-epos-server')
				{
					$eposPrinter = new Symbic_Printing_Epson_Epos_Server($printer['options']);
					$eposRequest = $eposPrinter->newRequest();

					foreach ($codes as $code) {
						if ($values['headline'] != '') {
							$eposRequest->addTextLn($values['headline']);
						}
						$eposRequest->addTextLn('Teilnehmercode: ' . $code['code']);
						$eposRequest->addCut();
					}


					$eposResponse = $eposPrinter->sendRequest($eposRequest);
					$this->view->printRequest = $eposRequest->toString();
					$this->view->printResponse = $eposResponse->getContent();
					$this->view->message = 'Request send';
					$this->_helper->viewRenderer('printcodesnippets-epson-epos-server');
					return;
				} else {
					throw new Exception('Unsupported printer type: ' . $printer['type']);
				}


				/*elseif ($printer['type']=='epson-epos-browser')
				{
					$this->_helper->viewRenderer('printcodesnippets-epson-epos-browser');
					return;
				} */
			}
		}
		$this->view->form = $form;
	}

	public function codelabelsAction()
	{
		// TODO: get all following parameters from a form
		// TODO: add predefined settings to the view = set form values for label type istead of hardcode them here
		// TODO: implement skip labels number x,y,z,... or skip first x labels

		// override default label type
		$labelType = $this->getParam('labelType', null);

		// Avery Zweckform: 3653, 3474
		// letter
		//$pageHeight = 279;
		//$pageWidth = 216;
		
		switch ($labelType)
		{
			case 'HERMA_No._4452':
				// 105 x 41.25 mm
				$labelRowCount = 7;
				$labelColCount = 2;

				$pageHeight = 297;
				$pageWidth = 210;
				$pageMarginLeft = 0;
				$pageMarginRight = 0;
				$pageMarginTop = 4.125;
				$pageMarginBottom = 4.125;
				
				$labelMarginLeft = 2;
				$labelMarginRight = 2;
				$labelMarginTop = 2;
				$labelMarginBottom = 2;

				$labelPaddingLeft = 2;
				$labelPaddingRight = 2;
				$labelPaddingTop = 2;
				$labelPaddingBottom = 2;
				
				$font = 'Arial';
				$fontMode = 'B';
				$fontSize = 12;
				$fontLineHeight = 14;
				$fontAlign = 'center';
				$fontVAlign = 'center';
				
				$labelBorder = 0;
				break;

			case 'HERMA_No._4615':
				// 70 x 37.125mm
				$labelRowCount = 7;
				$labelColCount = 3;
				
				$pageHeight = 297;
				$pageWidth = 210;
				$pageMarginLeft = 0;
				$pageMarginRight = 0;
				$pageMarginTop = 0;
				$pageMarginBottom = 0;
				
				$labelMarginLeft = 2;
				$labelMarginRight = 2;
				$labelMarginTop = 2;
				$labelMarginBottom = 2;

				$labelPaddingLeft = 2;
				$labelPaddingRight = 2;
				$labelPaddingTop = 2;
				$labelPaddingBottom = 2;
				
				$font = 'Arial';
				$fontMode = 'B';
				$fontSize = 12;
				$fontLineHeight = 14;
				$fontAlign = 'center';
				$fontVAlign = 'center';
				
				$labelBorder = 0;
				break;
				
			case 'HERMA_No._4459':
				// 70 x 16.93mm
				$labelRowCount = 17;
				$labelColCount = 3;
				
				$pageHeight = 297;
				$pageWidth = 210;
				$pageMarginLeft = 0;
				$pageMarginRight = 0;
				$pageMarginTop = 4.6;
				$pageMarginBottom = 4.6;
				
				$labelMarginLeft = 2;
				$labelMarginRight = 2;
				$labelMarginTop = 2;
				$labelMarginBottom = 2;

				$labelPaddingLeft = 2;
				$labelPaddingRight = 2;
				$labelPaddingTop = 2;
				$labelPaddingBottom = 2;
				
				$font = 'Arial';
				$fontMode = 'B';
				$fontSize = 12;
				$fontLineHeight = 14;
				$fontAlign = 'center';
				$fontVAlign = 'center';
				
				$labelBorder = 0;
				break;
				
			/*case 'HERMA_No._4609':
				// 52.5 x 21.2mm
				$labelRowCount = 12;
				$labelColCount = 4;
				break;

			case 'HERMA_No._4271':
				// 48.3 x 16.9 mm
				$labelRowCount = 12;
				$labelColCount = 4;
				break;

			case 'HERMA_No._4271':
				// 52.5 x 21.2mm
				$labelRowCount = 12;
				$labelColCount = 4;
				break;
			*/

			default:
				// What type of label is this?
				$labelRowCount = 8;
				$labelColCount = 2;
		}

		// prepare codes and dynamically scale font
		$codesSelect = Sophie_Db_Session_Participant :: getInstance()->select()->where('sessionId = ?', $this->session->id);
		$order = $this->_getParam('order', '');
		if ($order == 'rand')
		{
			$codesSelect->order(new Zend_Db_Expr('Rand()'));
		}
		else
		{
			$codesSelect->order('code ASC');
		}

		$participants = Sophie_Db_Session_Participant :: getInstance()->fetchAll($codesSelect);

		// prepare $pdf object to measure string width
		$pdf = new fpdf\FPDF('P', 'mm', array(
			$pageHeight,
			$pageWidth
		));
		$pdf->SetFont($font, $fontMode, $fontSize);

		// prepare label contents
		$labelMaxWidth = 0;
		$labelsContents = array();
		foreach ($participants as $participant)
		{
			$stringWidth = $pdf->GetStringWidth($this->session->name);
			if ($stringWidth > $labelMaxWidth)
			{
				$labelMaxWidth = $stringWidth;
			}
			
			$stringWidth = $pdf->GetStringWidth($participant->code);
			if ($stringWidth > $labelMaxWidth)
			{
				$labelMaxWidth = $stringWidth;
			}

			$lines = array();
			$lines[] = $this->session->name;
			$lines[] = $participant->code;
			$labelsContents[] = $lines;
		}
		
		// Produce PDF
		$cellHeight = (($pageHeight - ($pageMarginTop + $pageMarginBottom)) / $labelRowCount);
		$cellWidth = (($pageWidth - ($pageMarginLeft + $pageMarginRight)) / $labelColCount);
		
		$labelHeight = $cellHeight - $labelMarginTop - $labelMarginBottom;
		$labelWidth = $cellWidth - $labelMarginLeft - $labelMarginRight;

		$labelContentHeight = $labelHeight- $labelPaddingTop - $labelPaddingBottom;
		$labelContentWidth = $labelWidth - $labelPaddingLeft - $labelPaddingRight;
		
		if ($labelMaxWidth > $labelContentWidth)
		{
			$lineSpacing = $fontLineHeight - $fontSize;
			$fontSize = $fontSize * ($labelContentWidth / $labelMaxWidth);
			$fontLineHeight = $lineSpacing + $fontSize;
			$pdf->SetFont($font, $fontMode, $fontSize);
			
		}
		
		$pdf->SetTitle('SoPHIE Participant Code Labels', true);
		$pdf->SetCreator('SoPHIE', true);
		$pdf->SetAuthor('SoPHIE', true);
		$pdf->SetAutoPageBreak(false);
		$pdf->SetMargins(0, 0, 0);
		
		$i = 1;
		foreach ($labelsContents as $labelContents)
		{
			$page = floor(($i - 1) / ($labelRowCount * $labelColCount)) + 1;
			$i2 = $i - (($page - 1) * ($labelRowCount * $labelColCount));

			$row = floor(($i2 - 1) / ($labelColCount)) + 1;
			$col = $i2 - (($row - 1) * $labelColCount);

			if ($row == 1 && $col == 1)
			{
				$pdf->AddPage();
			}

			$x = $pageMarginLeft + ($col - 1) * $cellWidth;
			$y = $pageMarginTop + ($row - 1) * $cellHeight;
									
			if ($labelBorder == 1)
			{
				$pdf->Rect($x + $labelMarginLeft, $y + $labelMarginTop, $labelWidth, $labelHeight);
			}
			
			$ptToMm = 0.3527;
			$lineCounter = 1;
			
			$lineNumber = sizeof($labelContents);
			foreach ($labelContents as $labelContentLine)
			{
				$alignOffsetX = 0;
				$alignOffsetY = 0;
				
				if ($fontAlign == 'center')
				{
					$alignOffsetX = ($labelContentWidth - $pdf->GetStringWidth($labelContentLine)) / 2;
				}

				if ($fontVAlign == 'center')
				{
					$alignOffsetY = ($labelContentHeight - ($lineNumber * $fontLineHeight * $ptToMm)) / 2;
					
				}
				
				$pdf->Text($x + $alignOffsetX + $labelMarginLeft + $labelPaddingLeft, $y + $alignOffsetY + $labelMarginTop  + $labelPaddingTop + ($lineCounter * $fontLineHeight * $ptToMm), $labelContentLine);
				$lineCounter++;
			}

			$i++;
		}

		$pdf->Output();

		exit;
	}

	/*
	public function addAction()
	{
		$treatment = $this->session->findParentRow('Sophie_Db_Treatment');
		$types = $treatment->findDependentRowset('Sophie_Db_Treatment_Type');

		$form = $this->getForm('Participant_Add');
		$form->setAction('javascript:expadmin.sessionParticipantAdd()');

		$form->getElement('stepId')->setMultiOptions($treatmentStructure);
		$treatmentStructure = array();
		$stepgroups = $treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup', null, Sophie_Db_Treatment_Stepgroup::getInstance()->select()->order('position'));
		foreach ($stepgroups as $stepgroup) {
			$steps = $stepgroup->findDependentRowset('Sophie_Db_Treatment_Step', null, Sophie_Db_Treatment_Step::getInstance()->select()->order('position'));
			foreach ($steps as $step) {
				$treatmentStructure[$step->id] = $stepgroup->position . '.' . $step->position . ' : ' . $step->name;
			}
		}

		$typesOptions = array();
		foreach ($types as $type) {
			$typesOptions[$type->label] = $type->name;
		}
		$form->getElement('typeLabel')->setMultiOptions($typesOptions);

		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$values = $form->getValues($_POST);
				$values['sessionId'] = $this->session->id;

				$values['state'] = 'new';
				//if ($values['state'] == 'new')
				//{
					$values['stepgroupLabel'] = new Zend_Db_Expr('NULL');
					$values['stepgroupLoop'] = new Zend_Db_Expr('NULL');
					$values['stepId'] = new Zend_Db_Expr('NULL');
				//}
				//else
				//{
				//	$newStep = Sophie_Db_Treatment_Step :: getInstance()->find($values['stepId'])->current();
				//	$newStepgroup = $newStep->findParentRow('Sophie_Db_Treatment_Stepgroup');
				//	$values['stepgroupLabel'] = $newStepgroup->label;
				//}

				$sessionService = Sophie_Service_Session::getInstance();
				$sessionParticipantModel = Sophie_Db_Session_Participant :: getInstance();
				$sessionGroupModel = Sophie_Db_Session_Group :: getInstance();

				if (!isset($values['code']) || $values['code'] == 'auto' || $values['code'] == '')
				{
					$autoCode = true;
				}
				else
				{
					$autoCode = false;
				}
				
				// TODO: break if failing for number of times
				do
				{
					$maxParticipantNumber = $sessionParticipantModel->fetchMaxNumberBySession($this->session->id);
					$maxTypeNumber = $sessionParticipantModel->fetchMaxTypeNumberBySession($this->session->id, $values['typeLabel']);
					$values['number'] = $maxParticipantNumber + 1;
					$values['label'] = $values['typeLabel'] . '.' . ($maxTypeNumber + 1);
					if ( $autoCode )
					{
						$values['code'] = $sessionService->generateCode();
					}
					else
					{
						if ($sessionParticipantModel->checkUniqueCode($values['code']))
						{
							$this->_helper->json(array(
								'type' => 'error',
								'message' => 'Participant code already exists'
							));
							return;
						}
					}
				}
				while (!$sessionParticipantModel->insert($values));

				Sophie_Db_Session_Log :: getInstance()->log($this->session->id, 'Added Participant ' . $values['label']);
				$this->_helper->json(array(
					'message' => 'Added participant ' . $values['label']
				));
				return;
			}
			else
			{
				$this->_helper->json(array(
					'type' => 'error',
					'message' => 'Creating participant failed '
				));
			}
		}

		$this->view->form = $form;
		$this->view->session = $this->session->toArray();
		$this->view->treatment = $treatment->toArray();
		//$this->view->treatmentStructure = $treatmentStructure;
		$this->_helper->layout->disableLayout();
	}
	*/

	public function editAction()
	{
		$participantId = $this->_getParam('participantId', 0);
		if ($participantId == 0) {
			$this->_error('Missing parameter');
			return;
		}

		$participant = Sophie_Db_Session_Participant :: getInstance()->find($participantId)->current();

		if (is_null($participant) || $participant->sessionId != $this->session->id) {
			$this->_error('Selected participant does not exist or does not belong to selected session!');
			return;
		}

		$treatment = $this->session->findParentRow('Sophie_Db_Treatment');
		$types = $treatment->findDependentRowset('Sophie_Db_Treatment_Type');

		$treatmentStructure = array();
		$stepgroups = $treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup', null, Sophie_Db_Treatment_Stepgroup::getInstance()->select()->order('position'));
		foreach ($stepgroups as $stepgroup) {
			$steps = $stepgroup->findDependentRowset('Sophie_Db_Treatment_Step', null, Sophie_Db_Treatment_Step::getInstance()->select()->order('position'));
			foreach ($steps as $step) {
				$treatmentStructure[$step->id] = $stepgroup->position . '.' . $step->position . ' : ' . $step->name;
			}
		}

		$form = $this->getForm('Participant_Edit');
		$form->getElement('stepId')->setMultiOptions($treatmentStructure);

		$form->removeElement('typeLabel');
		/*$typesOptions = array();
		foreach ($types as $type) {
			$typesOptions[$type->label] = $type->name;
		}
		$form->getElement('typeLabel')->setMultiOptions($typesOptions);*/

		$form->setDefaults($participant->toArray());

		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$values = $form->getValues($_POST);
				$newStep = Sophie_Db_Treatment_Step :: getInstance()->find($values['stepId'])->current();
				$newStepgroup = $newStep->findParentRow('Sophie_Db_Treatment_Stepgroup');
				$values['stepgroupLabel'] = $newStepgroup->label;
				$this->_helper->flashMessenger('Changes to participant saved');
				$participant->setFromArray($values);
				$participant->save();
			}
		}

		$this->view->form = $form;
		$this->view->session = $this->session->toArray();
		$this->view->treatment = $treatment->toArray();
		$this->view->participant = $participant->toArray();
		$this->view->treatmentStructure = $treatmentStructure;
	}

	/*public function deleteAction()
	{
		$participantId = $this->_getParam('participantId', 0);
		if ($participantId == 0) {
			$this->_error('Missing parameter');
			return;
		}

		$participant = Sophie_Db_Session_Participant :: getInstance()->find($participantId)->current();

		if (is_null($participant) || $participant->sessionId != $this->session->id) {
			$this->_error('Selected participant does not exist or does not belong to selected session!');
			return;
		}

		$participant->state = 'deleted';
		$participant->save();
		//		$participant->delete();

		$message = 'Participant deleted';
		$this->_helper->json(array(
			'message' => $message
		));
	}*/

	/**
	 * Set a participant type to a step by their participant label
	 */
	public function edittypeAction()
	{
		$treatment = $this->session->findParentRow('Sophie_Db_Treatment');

		$treatmentStructure = array();
		$stepgroups = $treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup', null, Sophie_Db_Treatment_Stepgroup::getInstance()->select()->order('position'));
		foreach ($stepgroups as $stepgroup) {
			$steps = $stepgroup->findDependentRowset('Sophie_Db_Treatment_Step', null, Sophie_Db_Treatment_Step::getInstance()->select()->order('position'));
			foreach ($steps as $step) {
				$treatmentStructure[$step->id] = $stepgroup->position . '.' . $step->position . ' : ' . $step->name;
			}
		}

		//Retrieve participant types for select choice
		$participantTypes = $treatment->findDependentRowset('Sophie_Db_Treatment_Type');
		$types = array();
		foreach ($participantTypes as $type) {
			$types[$type->label] = $type->name;
		}

		$form = $this->getForm('Participant_Edit_Type');
		$form->setAction('javascript:expadmin.sessionParticipantEditType()');
		$form->getElement('stepId')->setMultiOptions($treatmentStructure);
		$form->getElement('participantLabel')->setMultiOptions($types);
		//$form->setDefaults();

		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$values = $form->getValues($_POST);
				$newStep = Sophie_Db_Treatment_Step :: getInstance()->find($values['stepId'])->current();
				$newStepgroup = $newStep->findParentRow('Sophie_Db_Treatment_Stepgroup');
				$values['stepgroupLabel'] = $newStepgroup->label;

				$db = Zend_Registry::get('db');

				$where[] = 'sessionId = ' . $this->session->id;
				$where[] = 'typeLabel = "' . $values['participantLabel'] . '"';

				$db->update('sophie_session_participant',
					array('state' => $values['state'],
						'stepgroupLabel' => $values['stepgroupLabel'],
						'stepgroupLoop' => $values['stepgroupLoop'],
						'stepId' => $values['stepId']), $where);

				$this->_helper->json(array(
					'message' => 'Changes to participants saved'
				));
				return;
			}
		}

		$this->view->form = $form;
		$this->view->session = $this->session->toArray();
		$this->view->treatment = $treatment->toArray();
		$this->view->treatmentStructure = $treatmentStructure;
		$this->_helper->layout->disableLayout();
	}


	public function editallAction()
	{
		$treatment = $this->session->findParentRow('Sophie_Db_Treatment');

		$treatmentStructure = array();

		$stepgroups = $treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup');
		foreach ($stepgroups as $stepgroup) {
			$steps = $stepgroup->findDependentRowset('Sophie_Db_Treatment_Step');
			foreach ($steps as $step) {
				$treatmentStructure[$step->id] = $stepgroup->position . '.' . $step->position . ' : ' . $step->name;
			}
		}

		$treatmentStructure = array();
		$stepgroups = $treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup', null, Sophie_Db_Treatment_Stepgroup::getInstance()->select()->order('position'));
		foreach ($stepgroups as $stepgroup) {
			$steps = $stepgroup->findDependentRowset('Sophie_Db_Treatment_Step', null, Sophie_Db_Treatment_Step::getInstance()->select()->order('position'));
			foreach ($steps as $step) {
				$treatmentStructure[$step->id] = $stepgroup->position . '.' . $step->position . ' : ' . $step->name;
			}
		}

		$form = $this->getForm('Participant_Edit_All');
		$form->setAction('javascript:expadmin.sessionParticipantEditAll()');
		$form->getElement('stepId')->setMultiOptions($treatmentStructure);
		//$form->setDefaults();

		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$values = $form->getValues($_POST);
				$newStep = Sophie_Db_Treatment_Step :: getInstance()->find($values['stepId'])->current();
				$newStepgroup = $newStep->findParentRow('Sophie_Db_Treatment_Stepgroup');
				$values['stepgroupLabel'] = $newStepgroup->label;

				$db = Zend_Registry::get('db');
				$db->update('sophie_session_participant', array('state' => $values['state'], 'stepgroupLabel' => $values['stepgroupLabel'], 'stepgroupLoop' => $values['stepgroupLoop'], 'stepId' => $values['stepId']), 'sessionId = ' . $this->session->id);

				$this->_helper->json(array(
					'message' => 'Changes to participants saved'
				));
				return;
			}
		}

		$this->view->form = $form;
		$this->view->session = $this->session->toArray();
		$this->view->treatment = $treatment->toArray();
		$this->view->treatmentStructure = $treatmentStructure;
		$this->_helper->layout->disableLayout();
	}
}