<?php
abstract class Symbic_Datatables_Abstract
{
	public $enableFilters = true;

	protected $language = 'en';
	protected $tableClass = 'display';

	// refers to iDisplayLength:
	protected $displayLength = 25;
	// refers to aLengthMenu:
	protected $displayLengthMenu = array(
		10 => '10',
		25 => '25',
		50 => '50',
		100 => '100',
		// -1 => 'All',
	);

	// refers to bLengthChange:
	protected $lengthChange = true;
	// refers to bFilter:
	protected $enableGeneralSearch = true;
	// refers to sPaginationType: (two_button|full_numbers)
	protected $paginationType = 'full_numbers';

	protected $defaultSortColumn = null;
	protected $defaultSortOrder = 'ASC';
	protected $showFooter = true;

	abstract protected function configureColumns();
	abstract protected function configureFilters();

	abstract protected function getTotalCount();
	abstract protected function getFilteredCount();

	abstract protected function getRows();

	// to be overwritten with a central, individual prepare method
	// called at the beginning of getData()
	protected function prepareGetMethods() { }

	protected $exportAsExcel = false;
	protected $exportTitle = '';

	// table id to be accessed by JavaScript, magically created if not explicitly defined
	protected $id = null;

	// will be filled by setParams() to be used by getFilteredCount() and getRows():
	protected $filters = array();
	protected $generalSearch = '';
	private $order = array();
	protected $limitOffset = 0;
	protected $limitRowCount = 25;
	protected $sEcho = null;

	// Zend_Controller_Action to access parameters
	protected $action = null;

	public $alert = null;

	private $_view = null;
	private $_configuredColumns = null;
	private $_configuredFilters = null;
	private $_filtersExternal = false;
	private $_filtersForm = false;
	private $_defaultSortColNo = null;
	private $_ajaxSource = null;

	//Setup for translation
	private $_t = null;

	public function __construct(Zend_Controller_Action $action)
	{
		$this->action = $action;
		$this->limitRowCount = $this->displayLength;
	}

	public function setDefaultFilters(Array $defaultFilters)
	{
		$this->filters = array();

		$filters = $this->_configureFilters();
		foreach ($filters as $id => $filter)
		{
			if (!$filter['_dummy'])
			{
				$this->filters[ $id ] = (isset($defaultFilters[ $id ])) ? (string)$defaultFilters[ $id ] : $filter[ 'defaultValue' ];
			}
		}
	}

	final public function __toString()
	{
		try
		{
			$return = $this->render();
			return $return;
		}
		catch (Exception $e)
		{
			$message = "Exception caught by DataTables: " . $e->getMessage()
					 . "\nStack Trace:\n" . $e->getTraceAsString();
			trigger_error($message, E_USER_WARNING);
			return '';
		}
	}

	final public function render(Zend_View_Interface $view = null)
	{
		$this->setView($view);

		$this->prepareHead();
		$this->getView()->inlineScript()->appendScript( $this->getScript() );

		return '<div class="dataTable">' . $this->getFilters() . $this->getTable() . '</div>';
	}

	final public function getData( $style = 'DataTable' )
	{
		$this->prepareGetMethods();

		$urlfilters = $this->sprintfEscape($this->getFiltersForUrl());

		// get data:
		$data = array();
		$cols = $this->_configureColumns();
		$rows = $this->getRows();

		foreach ($rows as $row)
		{
			$dataRow = array();
			foreach ($cols as $col)
			{
				$value = array();
				foreach ($col['keys'] as $key)
				{
					$value[$key] = (isset($row[ $key ])) ? $row[ $key ] : null;
				}
				if (count($value) <= 1)
				{
					$value = reset($value);
				}

				switch ($style)
				{
					case 'DataTable':
						if ($col['escape'])
						{
							$value = $this->escapeRecursive($value);
						}

						if (!empty($col['sprintf']))
						{
							$col['sprintf'] = str_replace('%urlfilters%', $urlfilters, $col['sprintf']);
							$value = (is_array($value)) ? vsprintf($col['sprintf'], $value) : sprintf($col['sprintf'], $value);
						}
						break;
					case 'Export':
						if (is_string($col['export']))
						{
							$col['export'] = str_replace('%urlfilters%', $urlfilters, $col['export']);
							$value = (is_array($value)) ? vsprintf($col['export'], $value) : sprintf($col['export'], $value);
						}
				}
				$dataRow[ $col['id'] ] = $value;
			}
			$data[] = $dataRow;
		}

		switch ($style)
		{
			case 'DataTable':
				return array(
					'iTotalRecords' => $this->getTotalCount(),
					'iTotalDisplayRecords' => $this->getFilteredCount(),
					'sEcho' => $this->sEcho,
					'aaData' => $data,
					'XaaFilters' => $this->filters,
					'XsAlert' => $this->alert,
					'XsUrlFilters' => $this->getFiltersForUrl( true ),
				);
			case 'Export':
				return $data;
			default:
				throw new Exception('Unknown getData style: "' . $style . '"');
		}
	}

	final public function setParams()
	{
		// check if the request comes from an export button
		if ($this->action->hasParam('__export'))
		{
			return $this->_export();
		}

		// check if the request comes by XHR from DataTables:
		$this->sEcho = $this->action->getParam('sEcho', null);
		$id = $this->action->getParam('id', null);
		if (is_null($this->sEcho) || $id != $this->getId())
		{
			return false;
		}
		$this->sEcho = (int)$this->sEcho;

		return $this->_setParams();
	}

	final private function _setParams()
	{
		$this->setFilterParamsFromUrl();
		if ($this->action->getParam('id', null) !== $this->getId())
		{
			return false;
		}
		$paramFilters = $this->action->getParam('_filters', null);
		if (!is_array($paramFilters))
		{
			$paramFilters = array();
		}

		$filters = $this->_configureFilters();
		$this->_setDefaultFilters();
		if ($this->action->hasParam('__reset'))
		{
			// reset to default values: do not parse request
			return true;
		}
		foreach ($filters as $key => $filter)
		{
			if ($filter['_dummy'])
			{
				continue;
			}

			$this->filters[$key] = '';
			if ($this->action->hasParam('filter_' . $key))
			{
				$this->filters[$key] = $this->action->getParam('filter_' . $key, '');
			}
			else if (isset($paramFilters[$key]))
			{
				$this->filters[$key] = $paramFilters[$key];
			}

			if (
				$filter['type'] == 'select'
			 && isset($filter['defaultValue'])
			 && (
					(
						is_numeric($this->filters[$key])
					 && $this->filters[$key] < 0
					)
				 || (
						!isset($filter['values'][$this->filters[$key]])
					)
				)
			)
			{
				$this->filters[$key] = $filter['defaultValue'];
			}
		}

		$this->generalSearch = $this->action->getParam('sSearch', '');;

		$this->order = array();
		$iSortingCols = (int)$this->action->getParam('iSortingCols', 0);
		$cols = $this->_configureColumns();
		$colKeys = array();
		foreach ($cols as $col)
		{
			$colKeys[] = $col['orderByKey'];
		}
		for ($i = 0; $i <= $iSortingCols - 1; $i++)
		{
			$iSortCol = (int)$this->action->getParam('iSortCol_' . $i, -1);
			$sSortDir = strtolower($this->action->getParam('sSortDir_' . $i, 'asc'));
			$sSortDir = ($sSortDir == 'asc') ? 'ASC' : 'DESC';
			if (isset($colKeys[ $iSortCol ]))
			{
				foreach ($colKeys[ $iSortCol ] as $key)
				{
					$this->order[] = $key . ' ' . $sSortDir;
				}
			}
		}

		$this->limitOffset = (int)$this->action->getParam('iDisplayStart', 0);
		$this->limitRowCount = (int)$this->action->getParam('iDisplayLength', $this->displayLength);
		if ($this->limitRowCount <= 0)
		{
			$this->limitRowCount = PHP_INT_MAX;
		}

		return true;
	}

	final private function _export()
	{
		// prepare general export:
		if (!$this->_setParams())
		{
			return false;
		}
		// always export all rows:
		$this->limitOffset = 0;
		$this->limitRowCount = PHP_INT_MAX;

		// continue with corresponding format
		$type = $this->action->getParam('__export', null);
		switch ($type)
		{
			case 'xls':
				return $this->_exportXls();
			default:
				throw new Exception('Unknown export format: "' . $type . '"');
		}
	}

	final private function _exportXls()
	{
		if (!$this->exportAsExcel)
		{
			throw new Exception('Excel export is not enabled');
		}

		$export = new Symbic_Datatables_Export_Excel($this->exportTitle, $this->_configureColumns(), $this->getData('Export'));
		return $export->export();
	}

	final public function prepareHead()
	{
		$view = $this->getView();
		$dataTablesDir = $this->getDir();
		$view->jQuery();
		$view->inlineScript()->appendFile($dataTablesDir . '/js/jquery.dataTables.min.js');
		$view->headLink()->appendStylesheet($dataTablesDir . '/css/jquery.dataTables.css', 'all');
	}

	final public function getTable()
	{
		$view = $this->getView();
		$table = '<table cellpadding="0" cellspacing="0" border="" class="' . $view->escape($this->tableClass) . '" id="' . $view->escape($this->getId()) . '">';
		$th = $this->getTableHeadrow();
		$table .= '<thead>' . $th . '</thead>';

		$cols = $this->_configureColumns();
		$data = $this->getData();

		$table .= '<tbody>';
		if (empty($data['aaData']))
		{
			$table .= '<tr><td colspan="' . count($cols) . '" class="dataTables_empty">';
			$table .= $this->translate('Keine Einträge vorhanden.');
			$table .= '</td></tr>';
		}

		foreach ($data['aaData'] as $row)
		{
			$table .= '<tr>';
			foreach ($cols as $colId => $col)
			{
				$table .= '<td';
				if (count($col['classes']))
				{
					$table .= ' class="' . implode(' ', $col['classes']) . '"';
				}
				$table .= '>';
				$table .= (isset($row[$colId])) ? $row[$colId] : '&ndash;';
				$table .= '</td>';
			}
			$table .= '</tr>';
		}
		$table .= '</tbody>';

		if ($this->showFooter)
		{
			$table .= '<tfoot>' . $th . '</tfoot>';
		}
		$table .= '</table>';

		if ($data['iTotalDisplayRecords'] != 0)
		{
			$currentPage = ceil(($this->limitOffset + 1) / $this->limitRowCount);
			$totalPages = ceil($data['iTotalDisplayRecords'] / $this->limitRowCount);
			$pages = array(
				1,
				$currentPage,
				$totalPages,
			);

			$pageDisplayStart = array();
			foreach ($pages as $page)
			{
				$pageDisplayStart[$page] = ($page - 1) * $this->limitRowCount;
				// padding around pages:
				for ($i = 1; $i <= 2; $i++)
				{
					if ($page - $i > 1)
					{
						$pageDisplayStart[$page - $i] = ($page - $i - 1) * $this->limitRowCount;
					}
					if ($page + $i < $totalPages)
					{
						$pageDisplayStart[$page + $i] = ($page + $i - 1) * $this->limitRowCount;
					}
				}
			}
			ksort($pageDisplayStart);

			$pageBaseUrl = $view->url() . '?filters=' . /* $this->getFiltersForUrl() . */'&amp;id=' . urlencode($this->getId()) . '&amp;iDisplayStart=';

			$table .= '<noscript>';
			$table .= 'Seite: ';
			foreach ($pageDisplayStart as $page => $displayStart)
			{
				if ($page > 1 && !isset($pageDisplayStart[$page - 1]))
				{
					$table .= ' ... ';
				}
				if ($page == $currentPage)
				{
					$table .= '<strong>' . $page . '</strong>';
				}
				else
				{
					$table .= '<a href="' . $pageBaseUrl . $displayStart . '">' . $page . '</a>';
				}
				$table .= ' ';
			}
			$table .= '</noscript>';
		}
		return $table;
	}

	final private function getTableHeadrow()
	{
		$cols = $this->_configureColumns();
		$view = $this->getView();

		$th = '<tr>';
		foreach ($cols as $col)
		{
			$th .= '<th';
			if (count($col['classes']))
			{
				$th .= ' class="' . implode(' ', $col['classes']) . '"';
			}
			if (!empty($col['hoverTitle']))
			{
				$th .= ' title="' . ( ($col['escape']) ? $view->escape($col['hoverTitle']) : $col['hoverTitle'] ) . '"';
			}
			if (!empty($col['width']))
			{
				$th .= ' style="width:' . $col['width'] . '"';
			}
			$th .= '>' . ( ($col['escape']) ? $view->escape($col['title']) : $col['title'] ) . '</th>';
		}
		$th .= '</tr>';
		return $th;
	}

	final public function setAjaxSource( $url )
	{
		$this->_ajaxSource = $url;
	}

	final public function getScript( $encloseInScriptTags = false )
	{
		$view = $this->getView();

		$conf = array(
			'bProcessing' => true,
			'bServerSide' => true,
			'sAjaxSource' => (empty($this->_ajaxSource)) ? $view->url() : $this->_ajaxSource,
			'fnServerParams' => '%fnServerParams%',
			'fnPreDrawCallback' => '%fnPreDrawCallback%',
			'iDisplayLength' => $this->displayLength,
			'bLengthChange' => $this->lengthChange,
			'bFilter' => $this->enableGeneralSearch,
			'aLengthMenu' => array(array_keys($this->displayLengthMenu), array_values($this->displayLengthMenu)),
			'aoColumns' => array(),
			'sPaginationType' => $this->paginationType,
		);
		if (!empty($this->language))
		{
			switch ($this->language)
			{
				case 'en':
					break;
				case 'de':
				// case 'fr':
					$conf['oLanguage'] = array(
						'sUrl' => $this->getDir() . '/i18n/' . $this->language . '.json'
					);
					break;
				default:
					throw new Exception('Unknown language: ' . $this->language);
			}
		}

		$cols = $this->_configureColumns();
		foreach ($cols as $id => $col)
		{
			$colconf = array(
				'sName' => $col['id'],
				'mData' => $col['id'],
				'bSortable' => $col['sortable'],
				'sClass' => implode(' ', $col['classes']),
			);
			$conf['aoColumns'][] = $colconf;
		}
		if (!is_null($this->_defaultSortColNo))
		{
			$conf['aaSorting'] = array(array($this->_defaultSortColNo, ($this->defaultSortOrder == 'ASC') ? 'asc' : 'desc'));
		}

		$filters = $this->_configureFilters();
		$fnServerParams = 'function(aoData){';
		$fnServerParams .= 'aoData.push( { "name": "id", "value": "' . $this->getId() . '" } );';
		// check if there are external filters:
		if ($this->_filtersExternal)
		{
			foreach ($filters as $key => $filter)
			{
				if ($filter['_dummy'] || !in_array($filter['type'], array('externalInput', 'externalSelect')))
				{
					continue;
				}
				$fnServerParams .= 'aoData.push( { "name": "filter_' . $view->escape($key) . '", "value": ';
				switch ($filter['type'])
				{
					case 'externalInput':
						$fnServerParams .= '$("#' . $view->escape($filter['id']) . '")[0].value';
					break;
					case 'externalSelect':
						$fnServerParams .= '$("#' . $view->escape($filter['id']) . '")[0].options[$("#' . $view->escape($filter['id']) . '")[0].selectedIndex].value';
					break;
					default:
						$fnServerParams .= 'null';
				}
				$fnServerParams .= '} );';
			}
		}

		// check if filter form exists:
		if ($this->_filtersForm)
		{
			$fnServerParams .= 'if (!$("#' . $this->getId('form') . '")) { return; }';
			// check if hash parameter was set:
			$fnServerParams .= 'if ($("#' . $this->getId('form') . ' [name=__filter]").val() != "") {';
				$fnServerParams .= 'aoData.push( { "name": "filters", "value": $("#' . $this->getId('form') . ' [name=__filter]").val() });';
				$fnServerParams .= '$("#' . $this->getId('form') . ' [name=__filter]").val("");';
				$fnServerParams .= 'return;';
			$fnServerParams .= '}';
			foreach ($filters as $key => $filter)
			{
				if ($filter['_dummy'] || !in_array($filter['type'], array('input', 'select')))
				{
					continue;
				}
				$fnServerParams .= 'aoData.push( { "name": "filter_' . $view->escape($key) . '", "value": ';
				switch ($filter['type'])
				{
					case 'input':
						$fnServerParams .= '$("#' . $view->escape($filter['id']) . '")[0].value';
					break;
					case 'select':
						$fnServerParams .= '$("#' . $view->escape($filter['id']) . '")[0].options[$("#' . $view->escape($filter['id']) . '")[0].selectedIndex].value';
					break;
					default:
						$fnServerParams .= 'null';
				}
				$fnServerParams .= '} );';
			}
		}
		$fnServerParams .= '}';

		$fnPreDrawCallback = 'function (oSettings){';

			// $fnPreDrawCallback .= 'console.log(oSettings);';
			$fnPreDrawCallback .= 'var gotoStart = false;';

			$fnPreDrawCallback .= 'if (oSettings.fnRecordsDisplay() <= 0) {';
				$fnPreDrawCallback .= 'gotoStart = true;';
			$fnPreDrawCallback .= '} else if (oSettings.oInstance.fnGetData().length <= 0) {';
				$fnPreDrawCallback .= 'gotoStart = true;';
			$fnPreDrawCallback .= '}';

			$fnPreDrawCallback .= 'if (gotoStart && oSettings._iDisplayStart > 0) {';
				$fnPreDrawCallback .= 'oSettings.oInstance.fnPageChange("first");';
				$fnPreDrawCallback .= 'return false;';
			$fnPreDrawCallback .= '}';

			$fnPreDrawCallback .= 'return true;';

		$fnPreDrawCallback .= '}';

		$encodedConf = json_encode($conf);
		$encodedConf = str_replace('"%fnServerParams%"', $fnServerParams, $encodedConf);
		$encodedConf = str_replace('"%fnPreDrawCallback%"', $fnPreDrawCallback, $encodedConf);

		$script = '';
		if ($encloseInScriptTags)
		{
			$script .= '<script type="text/javascript" charset="utf-8">' . "\n";
		}
		$script .= '$(document).ready(function() {';

			$script .= 'if (window.location.hash) {';
				$script .= 'var h = window.location.hash;';
				$script .= 'var f = $("#' . $this->getId('form') . ' [name=__filter]");';
				$script .= 'if (f) {';
					$script .= 'f.val((h.indexOf("#") < 0) ? h : h.substr(1));';
				$script .= '}';
			$script .= '}';

			$script .= 'var oTable = $("#' . $this->getId() . '").dataTable( ' . $encodedConf . ');';
			$script .= 'oTable.bind("xhr", function(e, o, data){';
				foreach ($filters as $key => $filter)
				{
					if ($filter['_dummy'])
					{
						continue;
					}
					switch ($filter['type'])
					{
						case 'input':
							$script .= '$("#' . $view->escape($filter['id']) . '")[0].value = data.XaaFilters.' . $view->escape($key) . ';';
						break;
						case 'select':
							$script .= 'var sel = $("#' . $view->escape($filter['id']) . '")[0];';
							$script .= 'for (var i = 0; i < sel.options.length; i++) {';
								$script .= 'if (sel.options[i].value == data.XaaFilters.' . $view->escape($key) . ') {';
									$script .= 'sel.options[i].selected = true;';
									$script .= 'break;';
								$script .= '}';
							$script .= '}';
						break;
					}
				}
				$script .= 'if (data.XsAlert) { alert(data.XsAlert); }';
				$script .= 'if (data.XsUrlFilters) { var l=location.href;if(l.indexOf("#")>0){l=l.substr(0,l.indexOf("#"));}location.replace(l+"#"+data.XsUrlFilters); }';
			$script .= '});';

		$script .= '});';
		if ($encloseInScriptTags)
		{
			$script .= "\n" . '</script>';
		}
		return $script;
	}

	final public function getFilters()
	{
		$filters = $this->_configureFilters();
		$this->_setParams();

		$view = $this->getView();

		$refreshTableJs = '$(\'#' . $this->getId() . '\').dataTable().fnClearTable();';
		// collect default values for javascript reset to default values:
		$defaultValues = array();

		$result = '';

		foreach ($filters as $key => $filter) {
			if ($filter['type'] != 'select_input')
			{
				// type == select_input: already split in one select and one input
				$defaultValues[ $filter['id'] ] = $filter['defaultValue'];
			}
			if ($filter['_display'] == 'hidden')
			{
				continue;
			}
			$result .= '<fieldset>';
			if (isset($filter['title']))
			{
				$result .= '<legend>' . $view->escape($filter['title']) . '</legend>';
			}
			switch ($filter['type']) {
				case 'input':
					$result .= '<input type="text" name="_filters[' . $view->escape($key) . ']" value="' . $view->escape($this->filters[$key]) . '" id="' . $view->escape($filter['id']) . '"';
					if (isset($filter['class']) && !empty($filter['class']))
					{
						$result .= ' class="' . $filter['class'] . '"';
					}
					$result .= ' />';

				break;
				case 'select':
					$result .= '<select name="_filters[' . $view->escape($key) . ']" id="' . $view->escape($filter['id']) . '"';
					if (isset($filter['submitOnChange']) && $filter['submitOnChange'])
					{
						$result .= ' onchange="' . $refreshTableJs . '"';
					}
					if (isset($filter['class']) && !empty($filter['class']))
					{
						$result .= ' class="' . $filter['class'] . '"';
					}
					$result .= '>';
					foreach ($filter['values'] as $value => $title)
					{
						$result .= '<option value="' . $view->escape($value) . '"';
						if ((string)$value === (string)$this->filters[$key])
						{
							$result .= ' selected="selected"';
						}
						$result .= '>' . $view->escape($title) . '</option>';
					}
					$result .= '</select>';
				break;
				case 'select_input':
					$result .= '<select name="filters[' . $view->escape($key) . '_select]" id="' . $view->escape($filter['id']) . '_select"';
					if (isset($filter['class']) && !empty($filter['class']))
					{
						$result .= ' class="' . $filter['class'] . '"';
					}
					$result .= '>';
					foreach ($filter['select']['values'] as $value => $title)
					{
						$result .= '<option value="' . $view->escape($value) . '"';
						if ((string)$value === (string)$this->filters[$key . '_select'])
						{
							$result .= ' selected="selected"';
						}
						$result .= '>' . $view->escape($title) . '</option>';
					}
					$result .= '</select>';
					$result .= '<input type="text" name="filters[' . $view->escape($key) . '_input]" value="' . $view->escape($this->filters[$key . '_input']) . '" id="' . $view->escape($filter['id']) . '_input"';
					if (isset($filter['class']) && !empty($filter['class']))
					{
						$result .= ' class="' . $filter['class'] . '"';
					}
					$result .= ' />';
				break;
			}
			if (isset($filter['Submit'])) {
				$result .= ' <input type="submit" value="' . $view->escape($filter['Submit']) . '" />';
			}
			$result .= '</fieldset>' . "\n";
		}
		if (!empty($result))
		{
			$result = '<form action="' . $view->url() . '" onsubmit="' . $refreshTableJs . 'return false" id="' . $this->getId('form') . '" autocomplete="off" class="dataTable">' . $result;

			$result .= '<fieldset class="submit">';
			$result .= '<input type="submit" value="' . $view->escape($this->translate('Filter anwenden')) . '" />';

			$result .= '<input type="submit" name="__reset" value="' . $view->escape($this->translate('Filter zurücksetzen')) . '" />';

			if ($this->exportAsExcel)
			{
				$result .= '<input type="submit" name="__exportXls" value="' . $view->escape($this->translate('XLS')) . '" />';
			}
			$result .= '</fieldset>';

			$result .= '<input type="hidden" name="id" value="' . $view->escape($this->getId()) . '" />';
			$result .= '<input type="hidden" name="__filter" value="" />';

			$result .= '</form>';

			if (count($defaultValues))
			{
				$result .= '<script type="text/javascript">';
				$result .= '$(function() {';	// Handler for .ready() called
					// set reset button onclick:
					$result .= '$( "#' . $this->getId('form') . ' input[name=__reset]" ).click(function() {';
						$result .= '$.each(' . json_encode($defaultValues) . ', function(k, v) {';
							$result .= '$( "#" + k ).val(v);';
						$result .= '});';
						$result .= $refreshTableJs;
						$result .= 'return false;';
					$result .= '});';
				if ($this->exportAsExcel)
				{
					// set xls button onclick:
					$result .= '$( "#' . $this->getId('form') . ' input[name=__exportXls]" ).click(function() {';
						$result .= 'var f = document.getElementById("' . $this->getId('form') . '");';
						$result .= '$("<input>").attr({type: "hidden", name: "__export", value: "xls"}).appendTo(f);';
						$result .= 'f.submit();';
						$result .= 'return false;';
					$result .= '});';
				}
				$result .= '});';
				$result .= '</script>';
			}
		}
		return $result;
	}

	final public function getFiltersForUrl( $forUrlAnchor = false )
	{
		$filters = $this->_configureFilters();
		$result = array();
		foreach ($filters as $id => $filter)
		{
			if (isset($this->filters[$id]) && $this->filters[$id] != $filter['defaultValue'])
			{
				$result[$id] = $this->filters[$id];
			}
		}
		$result = urlencode(json_encode($result, JSON_FORCE_OBJECT) . '-' . $this->limitOffset . '-' . $this->limitRowCount . '-' . $this->getId());
		return ($forUrlAnchor) ? $result : ('filters=' . $result);
	}

	final public function getOrder()
	{
		if (empty($this->order) && !empty($this->defaultSortColumn))
		{
			$cols = $this->_configureColumns();
			if (isset($cols[$this->defaultSortColumn]))
			{
				foreach ($cols[$this->defaultSortColumn]['orderByKey'] as $key)
				{
					$this->order[] = $key . ' ' . $this->defaultSortOrder;
				}
			}
		}
		return $this->order;
	}


	final private function setFilterParamsFromUrl()
	{
		$request = $this->action->getRequest();
		$rawUrlFilterValue = $request->getQuery('filters', null);
		if (is_null($rawUrlFilterValue))
		{
			$rawUrlFilterValue = $this->action->getParam('filters', null);
		}
		if (is_null($rawUrlFilterValue) || !is_string($rawUrlFilterValue))
		{
			return;
		}
		$rawUrlFilterValue = urldecode($rawUrlFilterValue);
		if (!preg_match('/^{(.*)}-(\d+)-(\d+)-(.+)$/', $rawUrlFilterValue, $matches))
		{
			return;
		}

		$parameters = json_decode('{' . $matches[1] . '}', true);
		$parameters['iDisplayStart'] = $matches[2];
		$parameters['iDisplayLength'] = $matches[3];
		$parameters['id'] = $matches[4];

		foreach ($parameters as $key => $value)
		{
			if ($key !== 'iDisplayStart' && $key !== 'iDisplayLength' && $key !== 'id')
			{
				$key = 'filter_' . $key;
			}
			if (!$this->action->hasParam($key))
			{
				$this->action->setParam($key, $value);
			}
		}
	}

	final protected function sprintfEscape($txt)
	{
		return str_replace('%', '%%', $txt);
	}

	final private function _configureColumns()
	{
		if (null !== $this->_configuredColumns)
		{
			return $this->_configuredColumns;
		}

		$this->_configuredColumns = array();
		$cols = $this->configureColumns();

		if (!is_array($cols))
		{
			throw new Exception('configureColumns() must return an array, ' . gettype($cols) . ' given.');
		}
		$colNo = 0;
		foreach ($cols as $id => $col)
		{
			if (isset($col['keys']) && !isset($col['key']))
			{
				$col['key'] = $col['keys'];
			}
			if (!isset($col['key']) || !isset($col['title'])) {
				throw new Exception('configureColumns() must return an array of arrays with keys "key" (or "keys") and "title".');
			}
			if (is_numeric($id))
			{
				$id = 'col' . $colNo;
			}
			if (!is_array($col['key']))
			{
				$col['key'] = array($col['key']);
			}

			if (!isset($col['orderByKey']))
			{
				$col['orderByKey'] = (isset($col['orderByKeys'])) ? $col['orderByKeys'] : $col['key'];
			}
			if (!is_array($col['orderByKey']))
			{
				$col['orderByKey'] = array($col['orderByKey']);
			}

			$conf = array(
				'no' => $colNo,
				'id' => $id,
				'keys' => $col['key'],
				'orderByKey' => $col['orderByKey'],
				'title' => $this->translate($col['title']),
				'width' => null,
				'classes' => array(),
				'escape' => true,
				'sprintf' => null,
				'export' => true,
				'sortable' => false,
			);
			if (isset($col['width']) && preg_match('/^([0-9\.]+)(|px|em|\%|pt|pc|in|mm|cm|ex)$/i', $col['width'], $matches))
			{
				if (empty($matches[2]))
				{
					$col['width'] .= 'em';
				}
				$conf['width'] = $col['width'];
			}

			if (isset($col['align']) && preg_match('/^(right|center|left|justify)$/i', $col['align']))
			{
				$conf['classes'][] = $col['align'];
			}

			if (isset($col['nobr']) && $col['nobr'])
			{
				$conf['classes'][] = 'nowrap';
			}

			if (isset($col['escape']))
			{
				$conf['escape'] = (bool)$col['escape'];
			}

			if (isset($col['sprintf']))
			{
				$conf['sprintf'] = $col['sprintf'];
			}

			if (isset($col['export']))
			{
				$conf['export'] = $col['export'];
			}

			if (isset($col['sortable']))
			{
				$conf['sortable'] = (bool)$col['sortable'];
			}

			$this->_configuredColumns[$id] = $conf;

			if (isset($col['defaultSort']) && $col['defaultSort'] && is_null($this->_defaultSortColNo) && $conf['sortable'])
			{
				$this->_defaultSortColNo = $conf['no'];
			}

			$colNo++;
		}

		if (isset($this->_configuredColumns[ $this->defaultSortColumn ]) && $this->_configuredColumns[ $this->defaultSortColumn ]['sortable'])
		{
			$this->_defaultSortColNo = $this->_configuredColumns[ $this->defaultSortColumn ]['no'];
		}

		return $this->_configuredColumns;
	}

	public function removeFilter($name)
	{
		$this->_configureFilters();
		if(isset($this->_configuredFilters[$name]))
		{
			unset($this->_configuredFilters[$name]);
		}
	}

	final private function _configureFilters()
	{
		if (null !== $this->_configuredFilters)
		{
			return $this->_configuredFilters;
		}

		$this->_configuredFilters = array();
		$this->filters = array();

		if ($this->enableFilters == false)
		{
			return $this->_configuredFilters;
		}

		$filters = $this->configureFilters();
		// copy hidden filters for double filters:
		foreach ($filters as $id => $filter)
		{
			if ($filter['type'] == 'select_input')
			{
				$input = array(
					'_display' => 'hidden',
					'type' => 'input',
					'defaultValue' => (isset($filter[ 'defaultValue' ])) ? (string)$filter[ 'defaultValue' ] : null,
				);
				$filters[ $id . '_input'] = $input;
				$select = array(
					'_display' => 'hidden',
					'type' => 'select',
					'values' => $filter['select'][ 'values' ],
					'defaultValue' => (isset($filter['select'][ 'defaultValue' ])) ? (string)$filter['select'][ 'defaultValue' ] : null,
				);
				$filters[ $id . '_select'] = $select;
			}
		}

		$this->_filtersExternal = false;
		$this->_filtersForm = false;

		foreach ($filters as $id => $filter)
		{
			if ($filter['type'] === 'externalInput' || $filter['type'] === 'externalSelect')
			{
				if (!isset($filter['id']))
				{
					$filter['id'] = $id;
				}
				$this->_filtersExternal = true;
				$filter['_display'] = 'hidden';
			}
			else
			{
				$filter['id'] = $this->getId('filter') . '_' . $id;
				$this->_filtersForm = true;
			}
			$filter[ 'defaultValue' ] = (isset($filter[ 'defaultValue' ])) ? (string)$filter[ 'defaultValue' ] : null;

			if (!isset($filter['_display']))
			{
				$filter['_display'] = 'render';
			}
			$filter['_dummy'] = false;

			if ($filter['type'] == 'select_input')
			{
				$filter['_dummy'] = true;
				$filter['select']['defaultValue'] = (isset($filter['select'][ 'defaultValue' ])) ? (string)$filter['select'][ 'defaultValue' ] : null;
			}

			//Translation
			if(isset($filter['title']))
			{
				$filter['title'] = $this->translate($filter['title']);
			}
			if(isset($filter['Submit']))
			{
				$filter['Submit'] = $this->translate($filter['Submit']);
			}

			$this->_configuredFilters[$id] = $filter;
		}
		$this->_setDefaultFilters();

		return $this->_configuredFilters;
	}

	final private function _setDefaultFilters()
	{
		if (null !== $this->_configuredFilters)
		{
			$this->_configureFilters();
		}
		$filters = $this->_configuredFilters;

		$this->filters = array();
		foreach ($filters as $id => $filter)
		{
			if (!$filter['_dummy'])
			{
				$this->filters[ $id ] = $filter[ 'defaultValue' ];
			}
		}

	}

	final private function getDir()
	{
		return $this->getView()->baseUrl( true ) . '/_scripts/DataTables';
	}

	final public function setView(Zend_View_Interface $view = null)
	{
		if (null !== $view)
		{
			$this->_view = $view;
		}
	}

	final protected function getView()
	{
		if (null === $this->_view) {
			$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
			$this->_view = $viewRenderer->view;
		}
		return $this->_view;
	}

	final private function getId($prefix = '')
	{
		if (null === $this->id)
		{
			$this->id = 'table_' . get_class($this);
		}
		return $prefix . $this->id;
	}

	final private function escapeRecursive(&$value, $key = null)
	{
		$view = $this->getView();
		if (is_array($value))
		{
			array_walk_recursive($value, array($this, 'escapeRecursive'));
		}
		else
		{
			$value = $view->escape((string)$value);
		}
		return $value;
	}

	/**
	 * sets a Zend_Translate Object for translation if no one given a translate object
	 * is requested through registry
	 * @param Zend_Translate Object
	 * @return true if Object is successfully set
	 */
	final public function setTranslator(Zend_Translate $t = null)
	{
		if(is_null($t))
		{
			if(Zend_Registry::isRegistered('Zend_Translate'))
			{
				$this->_t = Zend_Registry::get('Zend_Translate');
			}
		} else {
			$this->_t = $t;
		}
		if(!is_null($this->_t))
		{
			$this->language =  $this->_t->getLocale();
		}
		return !is_null($this->_t);
	}
	/**
	 * internal translation wrapper that translates a string iff Zend Translate Object is given
	 * @param String to translate
	 * @return String
	 */
	final protected function translate($str)
	{
		if(is_null($this->_t))
		{
			return $str;
		}
		else
		{
			return $this->_t->_($str);
		}
	}

}