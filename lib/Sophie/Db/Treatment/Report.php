<?php
class Sophie_Db_Treatment_Report extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_treatment_report';
	public $_primary = 'id';

	public $_referenceMap = array (
		'Treatment' => array (
			'columns' => array (
				'treatmentId'
			),
			'refTableClass' => 'Sophie_Db_Treatment',
			'refColumns' => array (
				'id'
			)
		)
	);

	/**
	 * Returns a list of reports by the treatmentId
	 */
	public function getReportsByTreatmentId($treatmentId)
	{
		$where = $this->getAdapter()->quoteInto('treatmentId = ?', $treatmentId);
		return $this->fetchAll($where);
	}
}