<?php
class Symbic_Form_Decoratorset_Table extends Symbic_Form_Decoratorset_AbstractDecoratorset
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
			array(
				'formTag' => 'Form'
			),
			array(
				'class' => 'symbic_form',
				'role' => 'form'
			)
		)
	);

	protected $_subFormsFormDecorators = array (
		array (
			'FormElements'
		),
		array (
			'Description',
			array (
				'placement' => 'PREPEND',
				'tag' => 'p',
				'class' => 'symbic_form_description'
			)
		),
/*		array (
			array(
				'legendHeadline' => 'fieldset',
			),
			array (
				'class' => 'symbic_form_fieldset'
			)
		),*/
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
			array(
				'legendHeadline' => 'fieldset',
			),
			array (
				'class' => 'symbic_form_fieldset'
			)
		)
	);

	protected $_elementDefaultDecorators = array (
		//'ValidationAttributes',
		array(
			array(
				'translateAttributes' => 'TranslateAttributes'
			),
			array(
				'title',
				'alt',
				'placeholder'
			)
		),
		array(
			'AttributedViewHelper',
			array(
				'elementDefaultAttributes' => array('class' => 'form-control')
			)
		),
		'InputGroup',
		array (
			array (
				'form-group-element' => 'ErrorAwareHtmlTag'
			),
			array (
				'tag' => 'div',
				'class' => 'form-group'
			)
		),
		array(
			array(
				'elementErrors' => 'Errors'
			),
			array(
			)
		),
		array(
			array(
				'elementDescription' => 'Description'
			),
			array(
				'tag' => 'span',
				'tagClass' => 'help-block'
			)
		),
		array (
			array (
				'tableData' => 'HtmlTag'
			),
			array (
				'tag' => 'td',
			)
		),
		array (
			array(
				'label-th-end' => 'HtmlTag'
			),
			array (
				'tag' => 'th',
				'placement' => 'prepend',
				'closeOnly' => true
			)
		),
		array (
			array(
				'label' => 'ErrorAwareLabel'
			),
			array (
				'tag' => 'div',
				'placement' => 'prepend'
			)
		),
		array (
			array(
				'label-th-open' => 'HtmlTag'
			),
			array (
				'tag' => 'th',
				'placement' => 'prepend',
				'openOnly' => true
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
		'ButtonInput'		=> 'Button',

		'Reset'				=> 'Button',
		'ResetButton'		=> 'Reset',
		'ResetInput'		=> 'Reset',

		'SubmitButton'		=> 'Submit',
		'SubmitInput'		=> 'Submit',

		'ImageInput'		=> 'Submit',

		'Radio'				=> 'Checkbox',
		'RadioList'			=> 'Checkbox',
		
		'CaptchaInput'		=> 'Captcha',

		'Hash'				=> 'Hidden',

		'FileInput'			=> 'File'
	);

	protected $_elementDecorators = array (
		'Submit' => array (
			array(
				array(
					'translateAttributes' => 'TranslateAttributes'
				),
				array(
					'title',
					'alt',
					'placeholder',
					'content'
				)
			),
			array(
				'AttributedViewHelper',
				array(
					'elementDefaultAttributes' => array('class' => 'btn btn-primary pull-right')
				)
			),
			array(
				array(
					'elementDescription' => 'Description'
				),
				array(
					'tag' => 'span',
					'tagClass' => 'help-block'
				)
			),
			array (
				array (
					'tableData' => 'HtmlTag'
				),
				array (
					'tag' => 'td'
				)
			),
			array (
				array (
					'tableHeader' => 'HtmlTag'
				),
				array (
					'tag' => 'th',
					'placement' => 'prepend'
				)
			),
			array (
				array (
					'tableRow' => 'HtmlTagId'
				),
				array (
					'tag' => 'tr',
					'idPrefix' => 'formTr-',
					'class' => 'form-group'
				)
			)
		),
		'Button' => array (
			array(
				array(
					'translateAttributes' => 'TranslateAttributes'
				),
				array(
					'title',
					'alt',
					'placeholder',
					'content'
				)
			),
			array(
				'AttributedViewHelper',
				array(
					'elementDefaultAttributes' => array('class' => 'btn btn-default pull-right')
				)
			),
			array(
				array(
					'elementDescription' => 'Description'
				),
				array(
					'tag' => 'span',
					'tagClass' => 'help-block'
				)
			),
			array (
				array (
					'tableData' => 'HtmlTag'
				),
				array (
					'tag' => 'td'
				)
			),
			array (
				array (
					'tableHeader' => 'HtmlTag'
				),
				array (
					'tag' => 'th',
					'placement' => 'prepend'
				)
			),
			array (
				array (
					'tableRow' => 'HtmlTagId'
				),
				array (
					'tag' => 'tr',
					'idPrefix' => 'formTr-',
					'class' => 'form-group'
				)
			)
		),
		'CheckboxInlineLabel' => array(
			'ValidationAttributes',
			array(
				array(
					'translateAttributes' => 'TranslateAttributes'
				),
				array(
					'title',
					'alt',
					'placeholder'
				)
			),
			array(
				'AttributedViewHelper',
				array(
					'elementDefaultAttributes' => array('class' => 'checkbox')
				)
			),
			array(
				array(
					'elementDescription' => 'Description'
				),
				array(
					'tag' => 'span',
					'tagClass' => 'help-block'
				)
			),
			'Errors',
			array (
				array (
					'formGroup' => 'HtmlTag'
				),
				array (
					'tag' => 'div',
					'class' => 'checkbox'
				)
			),
			array (
				array (
					'tableData' => 'HtmlTag'
				),
				array (
					'tag' => 'td'
				)
			),
			array (
				array (
					'tableHeader' => 'HtmlTag'
				),
				array (
					'tag' => 'th',
					'placement' => 'prepend'
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
		),
		'Radio' => array (
			array(
				array(
					'translateAttributes' => 'TranslateAttributes'
				),
				array(
					'title',
					'alt',
					'placeholder'
				)
			),
			array(
				'AttributedViewHelper',
				array(
					'elementDefaultAttributes' => array('class' => 'radio')
				)
			),
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
		'File' => array (
			array(
				array(
					'translateAttributes' => 'TranslateAttributes'
				),
				array(
					'title',
					'alt',
					'placeholder'
				)
			),
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
		'StaticHtml' => array (
			array(
				array(
					'translateAttributes' => 'TranslateAttributes'
				),
				array(
					'value'
				)
			),
			'ViewHelper',
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
					'idPrefix' => 'formTr-',
					'class' => 'form-group'
				)
			),
		),
		'Hidden' => array (
			'ViewHelper',
			array (
				array (
					'tableData' => 'HtmlTag'
				),
				array (
					'tag' => 'td'
				)
			),
			array (
				array (
					'tableHeader' => 'HtmlTag'
				),
				array (
					'tag' => 'th',
					'placement' => 'prepend'
				)
			),
			array (
				array (
					'tableRow' => 'HtmlTagId'
				),
				array (
					'tag' => 'tr',
					'idPrefix' => 'formTr-',
					'class' => 'hidden form-group'
				)
			)
		),
	);
}