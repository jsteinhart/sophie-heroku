<?php
class Expadmin_ReportController extends Symbic_Controller_Action
{
	public function preDispatch()
	{
		$sessionId = $this->_getParam('sessionId', 0);
		if ($sessionId == 0) {
			$this->_error('Missing parameter sessionId');
			return;
		}

		$this->session = Sophie_Db_Session :: getInstance()->find($sessionId)->current();
		if (is_null($this->session)) {
			$this->_error('Selected session does not exist!');
			return;
		}

		$acl = System_Acl :: getInstance();
		if (!$acl->autoCheckAcl('session', $this->session->id, 'sophie_session')) {
			$this->_error('Access denied.');
			return;
		}

		$this->sessiontype = $this->session->findParentRow('Sophie_Db_Treatment_Sessiontype');
		$this->treatment = $this->session->findParentRow('Sophie_Db_Treatment');
	}

	public function codesAction()
	{
		$this->treatment = $this->session->findParentRow('Sophie_Db_Treatment');
		$this->view->treatment = $this->treatment->toArray();
		$this->view->session = $this->session->toArray();

		$this->_helper->layout->disableLayout();
	}

	public function payoffsAction()
	{
		$this->treatment = $this->session->findParentRow('Sophie_Db_Treatment');
		$this->view->treatment = $this->treatment->toArray();
		$this->view->session = $this->session->toArray();

		$this->_helper->layout->disableLayout();
	}

	public function treatmentAction()
	{
		$this->treatment = $this->session->findParentRow('Sophie_Db_Treatment');
		$this->reports = $this->treatment->findDependentRowset('Sophie_Db_Treatment_Report');
		$this->view->treatment = $this->treatment->toArray();
		$this->view->reports = $this->reports->toArray();
		$this->view->session = $this->session->toArray();

		$this->_helper->layout->disableLayout();
	}
	
	public function treatmentshowAction()
	{
		$reportId = $this->_getParam('reportId', null);
		if (is_null($reportId))
		{
			$this->_error('reportId parameter required');
			return;
		}
		
		$this->treatment = $this->session->findParentRow('Sophie_Db_Treatment');
		$reportTable = Sophie_Db_Treatment_Report::getInstance();
		$report = $reportTable->find($reportId)->current();

		if (is_null($report) || $report->treatmentId != $this->treatment->id)
		{
			$this->_error('Report not found or not associated to current treatment');
			return;
		}

		$context = new Sophie_Context();
		$context->setPersonContextLevel('none');
		$context->setProcessContextLevel('treatment');
		$context->setSession($this->session->toArray());

		$sandbox = new Sophie_Script_Sandbox();
		$sandbox->setContext($context);
		$sandbox->setLocalVars($context->getStdApis());
		$sandbox->setThrowOriginalException(true);

		try
		{
			Sophie_Eval_Error_Handler :: $context = $context;
			Sophie_Eval_Error_Handler :: $script = 'Treatment Report Action';
			Sophie_Eval_Error_Handler :: $printError = true;
			set_error_handler(array('Sophie_Eval_Error_Handler', 'errorHandler'));
			$reportReturn = $sandbox->run($report->definition);
			
			Sophie_Eval_Error_Handler :: $printError = false;
			restore_error_handler();
		}
		catch (Exception $e)
		{
			echo 'Running report script failed: ' . $e->getMessage();
			print_r($e);
			exit;
			//$this->_error('Running report script failed: ' . print_r($e, true));
		}
		
		switch ($report->type)
		{
			case 'php-raw-output':
				echo $sandbox->getEvalOutput();
				exit;
				break;
				
			default:
				$this->_error('Report type not implemented');
		}
	}
	
	public function payoffAction()
	{
		$orderBy = $this->_getParam('orderBy', 'code');

		$sessionPayoff = new Sophie_Service_Session_Payoff();
		$calculation = $sessionPayoff->calculate($this->session->id);

		$payoffs = array();
		$moneyPayoffs = array();
		$moneyPayouts = array();
		$secondaryPayoffs = array();
		$secondaryMoneyPayoffs = array();
		$secondaryMoneyPayouts = array();

		extract($calculation, EXTR_IF_EXISTS);

		$participants = Sophie_Db_Session_Participant :: getInstance()->fetchAll(Sophie_Db_Session_Participant :: getInstance()->select()->where('sessionId = ?', $this->session->id)->order($orderBy));

		$outputFormat = $this->_getParam('outputFormat', 'html');

		switch ($outputFormat) {
			case 'html':
				$this->_helper->layout->setLayout('popup');
				break;

			case 'pdf':

				$template = $this->_getParam('template', null);
				if (!is_null($template))
				{
					$config = Zend_Registry::get('config');
					if (isset($config['systemConfig']['sophie']['expadmin']) && isset($config['systemConfig']['sophie']['expadmin']['payoffReceiptPdfTemplates']) && is_array($config['systemConfig']['sophie']['expadmin']['payoffReceiptPdfTemplates']) && isset($config['systemConfig']['sophie']['expadmin']['payoffReceiptPdfTemplates'][$template]))
					{
						$template = $config['systemConfig']['sophie']['expadmin']['payoffReceiptPdfTemplates'][$template];
						if (!file_exists($template['file']))
						{
							$this->_error('Template file does not exist: ' . $template['file']);
							return;
						}
					}
					else
					{
						$this->_error('Template is not configured');
						return;
					}
				}

				$pdf = new fpdi\FPDI('P', 'mm', array(
					297,
					210
				));

				//n		$pdf->SetAutoPageBreak(0);
				//n		$pdf->SetMargins(0, 0, 0);

				$nf = new Pear_Numbers_Words();

				foreach ($participants as $participant) {
					$pdf->AddPage();

					if (!is_null($template))
					{
						$pdf->setSourceFile($template['file']);
						$pdf->useTemplate($pdf->ImportPage(1));
					}

					$pdf->SetFont('Helvetica', '', 14);
					// TODO: use treatment locale
					$pdf->SetXY(111.5, 58.5);
					$pdf->Cell(34, 16, number_format($moneyPayouts[$participant->label], 2, ",", "."), 0, 'r', 0);

					// TODO: use treatment locale
					$moneyPayoutAsText = $nf->toWords(floor($moneyPayouts[$participant->label]), 'de') . ' Euro';
					$moneyPayoutCents = ($moneyPayouts[$participant->label] - floor($moneyPayouts[$participant->label])) * 100;
					//if ($moneyPayoutCents > 0) {
						$moneyPayoutAsText .= ' und ' . $nf->toWords($moneyPayoutCents, 'de') . ' Cent';
					//}
					$moneyPayoutAsText = ucfirst($moneyPayoutAsText);

					$pdf->SetFont('Helvetica', '', 16);
					$pdf->Text(28, 95.5, $moneyPayoutAsText);

					$pdf->SetFont('Helvetica', '', 12);
					$pdf->Text(56, 162.5, date('d.m.Y'));
				}

				$pdf->Output();
				$this->_helper->layout->disableLayout();
				break;

			case 'csv':
				// TODO: implement csv list?
				die('Not implemented yet');
				$this->getResponse()->setHeader('Content-Type', 'text/csv');
				$this->getResponse()->setHeader('Content-disposition', 'attachment;filename=payofflist_session' . $this->session->id . '.csv');
				$this->_helper->layout->disableLayout();
				break;

			case 'xlsx':
				$this->getResponse()->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				$this->getResponse()->setHeader('Content-disposition', 'attachment;filename=payofflist_session' . $this->session->id . '.xlsx');
				$this->_helper->layout->disableLayout();
				break;

			default:
				throw new Exception('Output format for codes list is unkown: ' . $outputFormat);
		}

		$this->view->participants = $participants->toArray();
		$this->view->treatment = $this->treatment->toArray();
		$this->view->session = $this->session->toArray();

		$this->view->payoffs = $payoffs;
		$this->view->moneyPayoffs = $moneyPayoffs;
		$this->view->moneyPayouts = $moneyPayouts;

		if ($this->treatment->secondaryPayoffRetrivalMethod != 'inactive') {
			$this->view->secondaryPayoffs = $secondaryPayoffs;
			$this->view->secondaryMoneyPayoffs = $secondaryMoneyPayoffs;
			$this->view->secondaryMoneyPayouts = $secondaryMoneyPayouts;
		}

		$this->_helper->viewRenderer('payoff-' . $outputFormat);
	}
}