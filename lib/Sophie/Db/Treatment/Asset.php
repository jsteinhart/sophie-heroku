<?php
class Sophie_Db_Treatment_Asset extends Symbic_Db_Table_AbstractTable
{
	// CONFIG
	public $_name = 'sophie_treatment_asset';
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
	 * Returns a asset list of by the treatmentId
	 */
	public function getAssetsByTreatmentId($treatmentId)
	{
		$where = $this->getAdapter()->quoteInto('treatmentId = ?', $treatmentId);
		return $this->fetchAll($where);
	}

	/**
	 * Returns an asset by the treatmentId and label
	 */
	public function getAssetsByTreatmentIdAndLabel($treatmentId, $label)
	{
		$where = $this->getAdapter()->quoteInto('treatmentId = ?', $treatmentId);
		$where .= $this->getAdapter()->quoteInto(' AND label = ?', $label);
		return $this->fetchRow($where);
	}

	/**
	 * Check uniqueness by its treatmentId and label
	 */
	public function checkUnique($treatmentId, $label)
	{
		$where = array ();
		$where[] = $this->getAdapter()->quoteInto('treatmentId = ?', $treatmentId);
		$where[] = $this->getAdapter()->quoteInto('label = ?', $label);

		$result = $this->fetchAll($where)->toArray();

		return empty ($result) ? true : false;
	}

	/**
	 * Inserts a new asset if its unique by its treatmentId and label
	 */
	public function replace(array $data)
	{
		if ($this->checkUnique($data['treatmentId'], $data['label']))
		{
			return parent::insert($data);
		}
		else
		{
			$where = array ();
			$where[] = $this->getAdapter()->quoteInto('treatmentId = ?', $data['treatmentId']);
			$where[] = $this->getAdapter()->quoteInto('label = ?', $data['label']);
			$this->delete($where);

			return parent::insert($data);
		}
	}
}