<?php
class Sfwsysadmin_MailController extends Symbic_Controller_Action
{
	public function queueAction()
	{
		if (!$this->getModule()->isAllowed('mailQueue'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$mailQueueModel = $this->getModelSingleton('Mail_Queue');
		$result = $mailQueueModel->fetchAllOrderByColumn();
		$this->view->result = $result;
	}

	// TODO: failAction
	// TODO: resumeAction
	// TODO: freezeAction
	// TODO: sendAction
}