<?php
class Sophie_Db_Session_Parameter extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_session_parameter';
	public $_primary = 'id';

	public $_referenceMap = array (
		'Session' => array (
			'columns' => array (
				'sessionId'
			),
			'refTableClass' => 'Sophie_Db_Session',
			'refColumns' => array (
				'id'
			)
		)
	);
}