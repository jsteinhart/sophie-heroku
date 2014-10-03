<?php
class Sophie_Db_Session_Group extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_session_group';
	public $_primary = array('sessionId','label');

	public $_referenceMap    = array(
				'Session' => array(
            		'columns'           => array('sessionId'),
            		'refTableClass'     => 'Sophie_Db_Session',
            		'refColumns'        => array('id')
				));
}