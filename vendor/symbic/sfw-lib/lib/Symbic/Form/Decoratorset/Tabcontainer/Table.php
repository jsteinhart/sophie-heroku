<?php
class Symbic_Form_Decoratorset_Tabcontainer_Table extends Symbic_Form_Decoratorset_Table
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
			'Description',
			array (
				'placement' => 'PREPEND',
				'tag' => 'p',
				'class' => 'symbic_form_description'
			)
		),
		array (
			array(
				'legendHeadline' => 'fieldset',
			),
			array (
				'class' => 'symbic_form_fieldset'
			)
		),
		array (
			array(
				'formTag' => 'Form'
			),
			array(
				'class' => 'symbic_form',
				'role' => 'form'
			)
		)
	);

	protected $_subFormDecorators = array (
		array (
			'FormElements'
		),
		array (
			array(
				'subFormTable' => 'HtmlTag'
			),
			array (
				'tag' => 'table',
				'class' => 'symbic_form_table'
			)
		),
		array (
			'Description',
			array (
				'placement' => 'PREPEND',
				'tag' => 'p',
				'class' => 'symbic_form_description'
			)
		),
		array (
			'FormContentPane'
		)
	);

}