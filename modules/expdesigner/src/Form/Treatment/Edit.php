<?php
namespace Expdesigner\Form\Treatment;

class Edit extends \Symbic_Form_Standard
{
	protected $_defaultDecoratorsetClass = 'Symbic_Form_Decoratorset_Tabcontainer_Table';

	public function init()
	{
		$view = $this->getView();

		$this->setLegend('Edit Treatment');

		///////////////////////////////////

		$subformName = 'infoTab';
		$infoForm = $this->createSubForm();
		$infoForm->setAttribs(array(
			'name'   => $subformName,
			'legend' => 'Info',
			'dijitParams' => array(
				'title' => 'Info',
			),
		));

		$infoForm->addElement(
			'text',
			'name',
			array(
				'label'	  => 'Name',
				'required'	 => true
			)
		);

		$infoForm->addElement(
			'TextareaAutosize',
			'description',
			array(
				'label'		=> 'Description',
			)
		);

		$infoForm->addElement(
			'select',
			'state',
			array(
				'label' => 'State',
				'multiOptions' => array('template'=>'template', 'draft'=>'draft', 'used'=>'used', 'archiv'=>'archiv'),
				'required'	 => true
			)
		);

		$infoForm->addElement(
			'select',
			'defaultLocale',
			array(
				'label' => 'Locale',
				'required' => true,
				'multiOptions' => array('en_US'=>'en_US', 'de_DE'=>'de_DE')
			)
		);

		///////////////////////////////////

		//$tabOnShow = '';

		//$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'' . $subformName . '-ContentPane\'), \'onShow\', function() { ' . $tabOnShow . '} );');
		$infoForm->addElement('submit', 'submit', array('id'=> $subformName . 'Submit', 'label'=>'Save'));

		$this->addSubForm($infoForm, $subformName);

		////////////////////////////////////////////////////

		$subformName = 'payoffTab';
		$payoffForm = $this->createSubForm();
		$payoffForm->setAttribs(array(
			'name'   => $subformName,
			'legend' => 'Payoff',
			'dijitParams' => array(
				'title' => 'Payoff'
			)
		));

		$payoffForm->addElement(
			'select',
			'payoffRetrivalMethod',
			array(
				'label' => 'Retrieval method',
				'multiOptions' => array('payoffScript' => 'Use Payoff Script setting $payoff and $moneyPayoff',
		'payoffVarSum' => 'Sum of participant payoff variables (PE, PS and PSL)',
		'payoffSumVar' => 'Value of participant payoffSum variable (PE)'
		),
			'required' => true
			)
		);

		$payoffPayoffScript = $payoffForm->createElement(
			'CodemirrorTextarea',
			'payoffScript',
			array(
				'label' => 'Payoff Script',
				'toolbar' => new \Sophie_Toolbar_CodeMirror_Php(),
			)
		);
		$payoffForm->addElement($payoffPayoffScript);

		$payoffForm->addElement('StaticHtml', 'payoffScriptValidator', array (
			'value' => '<div id="payoffScriptSanitizerMessages" class="alert alert-danger" style="display:none;"></div>',
			'label' => '',
		));

		$payoffForm->addElement(
			'select',
			'secondaryPayoffRetrivalMethod',
			array(
				'label' => 'Secondary Retrieval method',
				'multiOptions' => array(
		'inactive' => 'Inactive',
		'secondaryPayoffScript' => 'Use Secondary Payoff Script setting $payoff and $moneyPayoff',
		'payoff2VarSum' => 'Sum of participant payoff2 variables (PE, PS and PSL)',
		'payoffSum2Var' => 'Value of participant payoffSum2 variable (PE)'
		),
			'required' => true
			)
		);

		$secondaryPayoffPayoffScript = $payoffForm->createElement(
			'CodemirrorTextarea',
			'secondaryPayoffScript',
			array(
				'label' => 'Secondary Payoff Script',
				'toolbar' => new \Sophie_Toolbar_CodeMirror_Php(),
			)
		);
		$payoffForm->addElement($secondaryPayoffPayoffScript);

		$payoffForm->addElement('StaticHtml', 'secondaryPayoffScriptValidator', array (
			'value' => '<div id="secondaryPayoffScriptSanitizerMessages" class="alert alert-danger" style="display:none;"></div>',
			'label' => '',
		));

		///////////////////////////////////

		$tabOnShow = $payoffPayoffScript->getJsInstance() . '.refresh();';
		$tabOnShow .= $secondaryPayoffPayoffScript->getJsInstance() . '.refresh();';

		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'' . $subformName . '-ContentPane\'), \'onShow\', function() { ' . $tabOnShow . '} );');
		$payoffForm->addElement('submit', 'submit', array('id'=> $subformName . 'Submit', 'label'=>'Save'));

		$this->addSubForm($payoffForm, $subformName);

		///////////////////////////////////

		$subformName = 'layoutTab';
		$layoutForm = $this->createSubForm();
		$layoutForm->setAttribs(array(
			'name'   => $subformName,
			'legend' => 'Layout',
			'dijitParams' => array(
				'title' => 'Layout',
			)
		));

		$layoutForm->addElement(
			'select',
			'layoutTheme',
			array(
				'label' => 'Theme',
				'multiOptions' => array(
					'' => 'Default'
				),
				//'required' => true,
				'autoInsertNotEmptyValidator' => false
			)
		);

		$layoutForm->addElement(
			'select',
			'layoutDesign',
			array(
				'label' => 'Design',
				'multiOptions' => array(
					'' => 'Default',
				),
				//'required' => true,
				'autoInsertNotEmptyValidator' => false
			)
		);

		$layoutCss = $layoutForm->createElement(
			'CodemirrorTextarea',
			'css',
			array(
				'label'				=> 'CSS',
				'CodeMirrorMode'	=> 'text/css'
			)
		);
		$layoutForm->addElement($layoutCss);

		///////////////////////////////////

		$tabOnShow = $layoutCss->getJsInstance() . '.refresh();';

		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'' . $subformName . '-ContentPane\'), \'onShow\', function() { ' . $tabOnShow . '} );');
		$layoutForm->addElement('submit', 'submit', array('id'=> $subformName . 'Submit', 'label'=>'Save'));

		$this->addSubForm($layoutForm, $subformName);

		///////////////////////////////////

		$subformName = 'screenTab';
		$screenForm = $this->createSubForm();
		$screenForm->setAttribs(array(
			'name'   => $subformName,
			'legend' => 'Custom Screens',
			'dijitParams' => array(
				'title' => 'Custom Screens',
			)
		));

		$toggleuUseCustomThemes = '(this.checked)'
			. '? jQuery("#formTr-createdHtml, #formTr-finishedHtml, #formTr-pausedHtml, #formTr-archivedHtml, #formTr-excludedHtml").show()'
			. ': jQuery("#formTr-createdHtml, #formTr-finishedHtml, #formTr-pausedHtml, #formTr-archivedHtml, #formTr-excludedHtml").hide();';
		$screenForm->addElement(
			'CheckboxInlineLabel',
			'useCustomThemes',
			array(
				'inlineLabel' => 'Use Custom Screens',
				'autoInsertNotEmptyValidator' => false,
				'onclick' => $toggleuUseCustomThemes,
			)
		);

		/////////

		$screenCreatedHtml = $screenForm->createElement(
			'SwitchCodemirrorWysiwygTextarea',
			'createdHtml',
			array(
				'label' => 'Created',
			)
		);
		$screenForm->addElement($screenCreatedHtml);

		$screenFinishedHtml = $screenForm->createElement(
			'SwitchCodemirrorWysiwygTextarea',
			'finishedHtml',
			array(
				'label' => 'Finished',
			)
		);
		$screenForm->addElement($screenFinishedHtml);

		$screenPausedHtml = $screenForm->createElement(
			'SwitchCodemirrorWysiwygTextarea',
			'pausedHtml',
			array(
				'label' => 'Paused',
			)
		);
		$screenForm->addElement($screenPausedHtml);

		$screenArchivedHtml = $screenForm->createElement(
			'SwitchCodemirrorWysiwygTextarea',
			'archivedHtml',
			array(
				'label' => 'Archived',
			)
		);
		$screenForm->addElement($screenArchivedHtml);

		$screenExcludedHtml = $screenForm->createElement(
			'SwitchCodemirrorWysiwygTextarea',
			'excludedHtml',
			array(
				'label' => 'Excluded',
			)
		);
		$screenForm->addElement($screenExcludedHtml);

		///////////////////////////////////

		$tabOnShow = $screenCreatedHtml->getJsInstance() . '.refresh();';
		$tabOnShow .= $screenFinishedHtml->getJsInstance() . '.refresh();';
		$tabOnShow .= $screenPausedHtml->getJsInstance() . '.refresh();';
		$tabOnShow .= $screenArchivedHtml->getJsInstance() . '.refresh();';
		$tabOnShow .= $screenExcludedHtml->getJsInstance() . '.refresh();';
		$tabOnShow .= 'if (!jQuery("#useCustomThemes").is(":checked")) { jQuery("#formTr-createdHtml, #formTr-finishedHtml, #formTr-pausedHtml, #formTr-archivedHtml, #formTr-excludedHtml").hide(); }';

		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'' . $subformName . '-ContentPane\'), \'onShow\', function() { ' . $tabOnShow . '} );');
		$screenForm->addElement('submit', 'submit', array('id'=> $subformName . 'Submit', 'label'=>'Save'));

		$this->addSubForm($screenForm, $subformName);

		///////////////////////////////////

		$subformName = 'setupTab';
		$setupForm = $this->createSubForm();
		$setupForm->setAttribs(array(
			'name'   => $subformName,
			'legend' => 'Setup',
			'dijitParams' => array(
				'title' => 'Setup',
			),
		));

		$setupScript = $setupForm->createElement(
			'CodemirrorTextarea',
			'setupScript',
			array(
				'label' => 'Setup Script',
			)
		);
		$setupForm->addElement($setupScript);

		$setupForm->addElement('StaticHtml', 'setupScriptValidator', array (
			'value' => '<div id="setupScriptSanitizerMessages" class="alert alert-danger" style="display:none;"></div>',
			'label' => '',
		));

		$tabOnShow = $setupScript->getJsInstance() . '.refresh();';
		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'' . $subformName . '-ContentPane\'), \'onShow\', function() { ' . $tabOnShow . '} );');

		$setupForm->addElement('submit', 'submit', array('id'=> $subformName . 'Submit', 'label'=>'Save'));

		$this->addSubForm($setupForm, $subformName);

		///////////////////////////////////
	}
}