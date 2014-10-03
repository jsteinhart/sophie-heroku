<?php
class Sophie_Db_Treatment_Type extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_treatment_type';
	public $_primary = array('treatmentId','label');

	public $_referenceMap    = array(
				'Treatment' => array(
            		'columns'           => array('treatmentId'),
            		'refTableClass'     => 'Sophie_Db_Treatment',
            		'refColumns'        => array('id')
				));

	// FUNCTIONS
	public function fetchRowByTreatmentAndLabel($treatmentId, $label)
	{
		return $this->fetchRow($this->select()->where('treatmentId = ?', $treatmentId)->where('label = ?', $label));
	}

	public function fetchRowByTreatmentAndName($treatmentId, $name)
	{
		return $this->fetchRow($this->select()->where('treatmentId = ?', $treatmentId)->where('name = ?', $name));
	}

	public function fetchAllByTreatmentExcludeType($treatmentId, $excludeTypeLabel)
	{
		$select = $this->select();
		$select->where('label <> ?', $excludeTypeLabel);
		$select->where('treatmentId = ?', $treatmentId);
		return $this->fetchAll($select);
	}
}