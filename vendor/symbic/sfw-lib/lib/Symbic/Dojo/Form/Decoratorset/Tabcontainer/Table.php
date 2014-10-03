<?php
class Symbic_Dojo_Form_Decoratorset_Tabcontainer_Table extends Symbic_Dojo_Form_Decoratorset_Table
{

	protected $_subFormsFormDecorators = array (
		array (
			'FormElements'
		),
		array (
			'TabContainer',
			array (
				'id' => 'tabContainer',
				'style' => 'width: 100%;',
				'dijitParams' => array (
					'tabPosition' => 'top',
					'doLayout' => 'false'
				)
			)
		),
		array (
			'HtmlTag',
			array (
				'tag' => 'div',
				'class' => 'symbic_dojo_form'
			)
		),
		array (
			'Description',
			array (
				'placement' => 'PREPEND',
				'tag' => 'p',
				'class' => 'sophie_dojo_form_description'
			)
		),
		'SymbicDijitForm',
		array (
			'HtmlTag',
			array (
				'tag' => 'div',
				'class' => 'symbic_dojo_form_container'
			)
		)
	);

	protected $_subFormDecorators = array (
		array (
			'FormElements'
		),
		array (
			'HtmlTag',
			array (
				'tag' => 'table',
				'class' => 'sophie_dojo_form'
			)
		),
		array (
			'Description',
			array (
				'placement' => 'PREPEND',
				'tag' => 'p',
				'class' => 'sophie_dojo_form_description'
			)
		),
		array (
			'FormContentPane'
		)
	);

}