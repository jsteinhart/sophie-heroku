<?php
/*
	TODO: Beispiel ergänzen um:
	- orderByKeys
	- Excel-Export (column config key 'export')
	- Filter vom Typ "externalInput" / "externalSelect"
*/
class Symbic_Datatables_Example extends Symbic_Datatables_Abstract
{
	protected $language = 'de';

	// refers to iDisplayLength:
	protected $displayLength = 25;
	// refers to bLengthChange:
	protected $lengthChange = true;
	protected $enableGeneralSearch = false;

	protected $defaultSortColumn = 'column3';

	protected function configureColumns()
	{
		return array(
			'column1' => array(
				'key' => 'rowA',
				'title' => 'Value A',
				'width' => 4,
				'align' => 'right',
				'escape' => false,
				'sortable' => true,
			),
			'column2' => array(
				'key' => 'rowB',
				'title' => 'Row B',
				'width' => '200px',
				'align' => 'center',
				'escape' => true,
				'sortable' => false,
				'sprintf' => 'Die %s ist hier.',
			),
			'column3' => array(
				'key' => 'rowC',
				'title' => 'A third Row',
				'width' => '20%',
				'align' => 'left',
				'escape' => true,
				'nobr' => true,
				'sortable' => true,
				// 'defaultSort' => true,
			)
		);
	}

	protected function configureFilters()
	{
		return array(
			'filterName' => array(
				'title' => 'Simple filter',
				'type' => 'input',
				'defaultValue' => '',
				'submit' => 'Apply'
			),
			'specialFilter' => array(
				'title' => 'Special filter',
				'type' => 'select',
				'values' => array(
					1 => 'a',
					2 => 'b',
					3 => 'c',
					'all' => 'all (a, b, c)',
				),
				'defaultValue' => 'all'
			)
		);
	}

	protected function getTotalCount()
	{
		return 400;
	}

	protected function getFilteredCount()
	{
		return 400;
	}

	protected function query()
	{
		$result = array();
		for (
			$i = ($this->page - 1) * 50 + 1;
			$i <= $this->page * 50;
			$i++
			) {
			$result[$i] = array(
				'rowB' => 'mittlere <Spalte>',
				'rowA' => 'links <strong>' . $i . '</strong>',
				'rowC' => 'Filter: ' . $this->filters['specialFilter'] . ' / ' . md5($i * $i)
			);
		}
		return $result;
	}
	
	protected function getRows()
	{
		// ...
	}
}