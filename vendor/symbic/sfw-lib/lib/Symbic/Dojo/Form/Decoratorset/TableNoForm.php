<?php
class Symbic_Dojo_Form_Decoratorset_TableNoForm extends Symbic_Form_Decoratorset_AbstractDecoratorset
{
	protected $_formDecorators = array (
		array (
			'FormElements'
		),
		array (
			'HtmlTag',
			array (
				'tag' => 'table',
				'class' => 'symbic_form_table'
			)
		),
		/*		array (
					'FormErrors',
					array (
						'class' => 'symbic_form_errors',
						'ignoreSubForms' => true
					)
				), */
		array (
			'Description',
			array (
				'placement' => 'PREPEND',
				'tag' => 'p',
				'class' => 'symbic_form_description'
			)
		),
		array (
			'fieldset',
			array (
				'class' => 'symbic_form_fieldset'
			)
		)
	);

	protected $_subFormsFormDecorators = array (
		array (
			'FormElements'
		),
		array (
			'HtmlTag',
			array (
				'tag' => 'table',
				'class' => 'symbic_form_table'
			)
		),
		/*		array (
					'FormErrors',
					array (
						'class' => 'symbic_form_errors',
						'ignoreSubForms' => true
					)
				), */
		array (
			'Description',
			array (
				'placement' => 'PREPEND',
				'tag' => 'p',
				'class' => 'symbic_form_description'
			)
		),
		array (
			'fieldset',
			array (
				'class' => 'symbic_form_fieldset'
			)
		),
		array (
			'DijitForm',
			'class' => 'symbic_form'
		)
	);


	protected $_subFormDecorators = array (
		array (
			'FormElements'
		),
		array(
			'HeaderRow'
		)
		/*		,
				array (
					'HtmlTag',
					array (
						'tag' => 'td',
						'class' => 'symbic_form_subformtable'
					)
				),
				array (
					'FormErrors',
					array (
						'class' => 'symbic_form_errors',
						'ignoreSubForms' => true
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
					'fieldset',
					array (
						'class' => 'symbic_form_fieldset'
					)
				),
				array (
					'Form',
					'class' => 'symbic_form'
				) */
	);

	protected $_elementDefaultDecorators = array (
		'SymbicDijitElement',
		'Description',
		'Errors',
		array (
			array (
				'tableData' => 'HtmlTag'
			),
			array (
				'tag' => 'td'
			)
		),
		array (
			'Label',
			array (
				'tag' => 'th'
			)
		),
		array (
			array (
				'tableRow' => 'HtmlTagId'
			),
			array (
				'tag' => 'tr',
				'idPrefix' => 'formTr-'
			)
		)
	);

	protected $_elementInheritDecorators = array (
		'Button' => 'Submit',
		'Reset' => 'Submit',
		'SubmitButton' => 'Submit',
	);

	protected $_elementDecorators = array (
		'Submit' => array (
			'Tooltip',
			'SymbicDijitElement',
			'Description',
			'Errors',
			array (
				array (
					'data' => 'HtmlTag'
				),
				array (
					'tag' => 'td',
					'colspan' => '2',
					'class' => 'submit'
				)
			),
			array (
				array (
					'row' => 'HtmlTag'
				),
				array (
					'tag' => 'tr'
				)
			)
		),
		'Hidden' => array (
			'SymbicDijitElement'
		),
		'Radio' => array (
			'SymbicDijitElement',
			'Description',
			'Errors',
			array (
				array (
					'tableData' => 'HtmlTag'
				),
				array (
					'tag' => 'td'
				)
			),
			array (
				'Label',
				array (
					'tag' => 'th',
					'disableFor' => true
				)
			),
			array (
				array (
					'tableRow' => 'HtmlTag'
				),
				array (
					'tag' => 'tr'
				)
			)
		),
		'Image' => array (
			'Tooltip',
			'Image',
			'Errors',
			array (
				array (
					'tableData' => 'HtmlTag'
				),
				array (
					'tag' => 'td'
				)
			),
			array (
				'Label',
				array (
					'tag' => 'th'
				)
			),
			array (
				array (
					'tableRow' => 'HtmlTag'
				),
				array (
					'tag' => 'tr'
				)
			)
		),
		'File' => array (
			'File',
			'Errors',
			'Description',
			array (
				array (
					'tableData' => 'HtmlTag'
				),
				array (
					'tag' => 'td'
				)
			),
			array (
				'Label',
				array (
					'tag' => 'th'
				)
			),
			array (
				array (
					'tableRow' => 'HtmlTag'
				),
				array (
					'tag' => 'tr'
				)
			)
		),
		'Captcha' => array (
			'Errors',
			'Description',
			array (
				array (
					'tableData' => 'HtmlTag'
				),
				array (
					'tag' => 'td'
				)
			),
			array (
				'Label',
				array (
					'tag' => 'th'
				)
			),
			array (
				array (
					'tableRow' => 'HtmlTag'
				),
				array (
					'tag' => 'tr'
				)
			)
		)
	);
}