<?php

class Symbic_Datatables_Export_Excel
{
	private $title = null;
	private $configuredColumns = null;
	private $data = null;

	private $phpExcel = null;
	private $sheet = null;

	public function __construct($title, $configuredColumns, $data)
	{
		$this->title = $title;
		$this->setColumns($configuredColumns);
		$this->data = $data;

		$this->phpExcel = new PHPExcel();
		$this->phpExcel->getProperties()->setTitle($this->title);

		if ($worksheet = $this->phpExcel->getSheetByName('Worksheet'))
		{
			$sheetIndex = $this->phpExcel->getIndex($worksheet);
			$this->phpExcel->removeSheetByIndex($sheetIndex);
		}

		$this->sheet = new PHPExcel_Worksheet($this->phpExcel, $this->title);
		$this->phpExcel->addSheet($this->sheet, 0);
	}

	public function setColumns($columns)
	{
		$this->configuredColumns = array();
		foreach ($columns as $id => $col)
		{
			if (!empty($col['export']))
			{
				$this->configuredColumns[ $id ] = $col;
			}
		}
	}

	public function export()
	{
		$this->writeHeaderRow();

		$this->writeData();

		$this->setAutoWidth();
		$this->output();
	}

	public function writeHeaderRow()
	{
		$colIndex = 1;
		$rowIndex = 1;

		$styleArray = array(
			'font' => array(
				'bold' => true,
			),
		);

		foreach ($this->configuredColumns as $column)
		{
			$coord = Symbic_Excel_Helper :: cellCoordinates($colIndex, $rowIndex);

			$this->sheet->setCellValue($coord, $column['title']);
			$this->sheet->getStyle($coord)->applyFromArray($styleArray);

			$colIndex++;
		}
	}

	public function writeData()
	{
		$colIndex = 1;
		$rowIndex = 1;

		foreach ($this->data as $row)
		{
			$rowIndex++;
			$colIndex = 1;
			foreach ($this->configuredColumns as $id => $column)
			{
				$v = $row[$id];
				if (is_array($v) || is_object($v))
				{
					$v = print_r($v, true);
				}
				elseif (is_null($v))
				{
					$v = '';
				}
				$this->sheet->setCellValue(Symbic_Excel_Helper :: cellCoordinates($colIndex, $rowIndex), (string)$v);
				$colIndex++;
			}
		}
	}

	public function setAutoWidth()
	{
		for($colIndex = 1; $colIndex <= count($this->configuredColumns); $colIndex++)
		{
			$this->sheet->getColumnDimension(Symbic_Excel_Helper :: cellCoordinates($colIndex, ''))->setAutoSize(true);
		}
		$this->sheet->calculateColumnWidths();
	}

	public function output()
	{
		ob_flush();
		$filename = $this->title . ' ' . date('Y-m-d His');
		$filename = preg_replace('/[^A-Za-z0-9 \\.,-_]/', ' ', $filename);
		$filename = preg_replace('/\h\h+/', ' ', $filename);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-disposition: attachment;filename="' . $filename . '.xlsx"');

		$excelWriter = PHPExcel_IOFactory :: createWriter($this->phpExcel, 'Excel2007');
		$excelWriter->save('php://output');
		exit;
	}
}