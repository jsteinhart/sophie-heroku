<?php
class Sophie_Db_Treatment_Screen extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_treatment_screen';
	public $_primary = 'treatmentId';

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
	
	public function getByTreatmentIdAndState($treatmentId, $state)
	{
		$screens = $this->find($treatmentId)->current();

		if (is_null($screens))
		{
			return '';
		}
		else
		{
			$screens = $screens->toArray();
		}
		
		if (empty($screens[$state]))
		{
			return '';
		}
		else
		{
			return $screens[$state];
		}
	}
}