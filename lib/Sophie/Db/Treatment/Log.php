<?php
class Sophie_Db_Treatment_Log extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_treatment_log';
	public $_primary = 'id';

	public $_referenceMap    = array(
				'Treatment' => array(
            		'columns'           => array('treatmentId'),
            		'refTableClass'     => 'Sophie_Db_Treatment',
            		'refColumns'        => array('id')
				));

	static public function log($treatmentId, $content, $type = 'notice')
	{
		$treatment = Sophie_Db_Treatment:: getInstance()->find($treatmentId)->current();

		if(!$treatment->loggingEnabled)
		{
			return;
		}
		$table = self::getInstance();
		$table->insert(array('treatmentId'=>$treatmentId, 'microtime'=>microtime(true), 'content'=>$content, 'type'=> $type));
	}
}