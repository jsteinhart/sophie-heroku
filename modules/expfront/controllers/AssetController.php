<?php
class Expfront_AssetController extends Symbic_Controller_Action
{

	public $_session = null;

	public function init()
	{
		$this->_session = new Zend_Session_Namespace('expfront');
	}

	public function indexAction()
	{
		if ( !isset($this->_session->participantId) )
		{
			$this->_error('unauthenticated');
			return;
		}

		if ( !$this->_hasParam('label'))
		{
			$this->_error('no label');
			return;
		}

		$db = Zend_Registry :: get('db');

		// get participant
		$participant = $db->fetchRow('SELECT * FROM sophie_session_participant WHERE id = ' . $db->quote($this->_session->participantId));
		if (!$participant)
		{
			$this->_session->unsetAll();
			$this->_error('Participant does not exists or does not exist any more');
			return;
		}

		$session = $db->fetchRow('SELECT * FROM sophie_session WHERE id = ' . $db->quote($participant['sessionId']));
		if (!$session)
		{
			$this->_session->unsetAll();
			$this->_error('Session does not exists or does not exist any more');
			return;
		}

		$assetTable = Sophie_Db_Treatment_Asset::getInstance();

		$assetLabel = $this->_getParam('label');
		$asset = $assetTable->getAssetsByTreatmentIdAndLabel($session['treatmentId'], $assetLabel);

		if (empty($asset))
		{
			echo 'File not found';
			exit;
		}
		
		header('Content-type: ' . $asset->contentType);
		echo $asset->data;
		exit;
	}
}