<?php
class Symbic_Form_Loader_Element extends Symbic_Loader_AliasMap
{
	protected $_filter = 'ucfirst';

	protected $_map = array(
		'Button'							=> 'Symbic_Form_Element_Button',
		'ButtonInput'						=> 'Symbic_Form_Element_ButtonInput',
		'Captcha'							=> 'Symbic_Form_Element_Captcha',
		'Checkbox'							=> 'Symbic_Form_Element_Checkbox',
		'CheckboxInlineLabel'				=> 'Symbic_Form_Element_CheckboxInlineLabel',
		'CodemirrorTextarea'				=> 'Symbic_Form_Element_CodemirrorTextarea',
		'ColorInput'						=> 'Symbic_Form_Element_ColorInput',
		'ComboBox'							=> 'Symbic_Form_Element_ComboBox',
		'DateInput'							=> 'Symbic_Form_Element_DateInput',
		'DatePicker'						=> 'Symbic_Form_Element_DatePicker',
		'DateRangePicker'					=> 'Symbic_Form_Element_DateRangePicker',
		'DatetimeInput'						=> 'Symbic_Form_Element_DatetimeInput',
		'DatetimeLocalInput'				=> 'Symbic_Form_Element_DatetimeLocalInput',
		'DateTimePicker'					=> 'Symbic_Form_Element_DateTimePicker',
		'DefaultOrNumberSpinner'			=> 'Symbic_Form_Element_DefaultOrNumberSpinner',
		'DefaultOrText'						=> 'Symbic_Form_Element_DefaultOrText',
		'DefaultOrTextInput'				=> 'Symbic_Form_Element_DefaultOrTextInput',
		'EmailInput'						=> 'Symbic_Form_Element_EmailInput',
		'FileInput'							=> 'Symbic_Form_Element_FileInput',
		'File'								=> 'Symbic_Form_Element_FileInput',
		'FloatInput'						=> 'Symbic_Form_Element_FloatInput',
		'Hash'								=> 'Symbic_Form_Element_Hash',
		'Hidden'							=> 'Symbic_Form_Element_Hidden',
		'ImageInput'						=> 'Symbic_Form_Element_ImageInput',
		'Image'								=> 'Symbic_Form_Element_ImageInput',
		'IntInput'							=> 'Symbic_Form_Element_IntInput',
		'MonthInput'						=> 'Symbic_Form_Element_MonthInput',
		'MultiCheckbox'						=> 'Symbic_Form_Element_MultiCheckbox',
		'MultiCheckboxBoxed'				=> 'Symbic_Form_Element_MultiCheckboxBoxed',
		'MultiEditor'						=> 'Symbic_Form_Element_MultiEditor',
		'Multiselect'						=> 'Symbic_Form_Element_Multiselect',
		'NumberSpinner'						=> 'Symbic_Form_Element_NumberSpinner',
		'PasswordInput'						=> 'Symbic_Form_Element_PasswordInput',
		'Password'							=> 'Symbic_Form_Element_PasswordInput',
		'Radio'								=> 'Symbic_Form_Element_Radio',
		'RadioList'							=> 'Symbic_Form_Element_RadioList',
		'RangeInput'						=> 'Symbic_Form_Element_RangeInput',
		'Reset'								=> 'Symbic_Form_Element_ResetInput',
		'ResetButton'						=> 'Symbic_Form_Element_ResetButton',
		'ResetInput'						=> 'Symbic_Form_Element_ResetInput',
		'SearchInput'						=> 'Symbic_Form_Element_SearchInput',
		'Select'							=> 'Symbic_Form_Element_Select',
		'StaticHtml'						=> 'Symbic_Form_Element_StaticHtml',
		'Submit'							=> 'Symbic_Form_Element_SubmitInput',
		'SubmitButton'						=> 'Symbic_Form_Element_SubmitButton',
		'SubmitInput'						=> 'Symbic_Form_Element_SubmitInput',
		'SwitchCodemirrorWysiwygTextarea'	=> 'Symbic_Form_Element_SwitchCodemirrorWysiwygTextarea',
		'TelInput'							=> 'Symbic_Form_Element_TelInput',
		'Textarea'							=> 'Symbic_Form_Element_Textarea',
		'TextareaAutosize'					=> 'Symbic_Form_Element_TextareaAutosize',
		'Text'								=> 'Symbic_Form_Element_TextInput',
		'TextInput'							=> 'Symbic_Form_Element_TextInput',
		'TimeInput'							=> 'Symbic_Form_Element_TimeInput',
		'TimePicker'						=> 'Symbic_Form_Element_TimePicker',
		'TimerInput'						=> 'Symbic_Form_Element_TimerInput',
		'UrlInput'							=> 'Symbic_Form_Element_UrlInput',
		'WeekInput'							=> 'Symbic_Form_Element_WeekInput'
	);

	public function activateDojoForms()
	{
		$elements = array(
			// Add Dojo Elements
			'Button'							=> 'Zend_Dojo_Form_Element_Button',
			'CheckBox'							=> 'Zend_Dojo_Form_Element_CheckBox',
			'ComboBox'							=> 'Zend_Dojo_Form_Element_ComboBox',
			'CurrencyTextBox'					=> 'Zend_Dojo_Form_Element_CurrencyTextBox',
			'DateTextBox'						=> 'Zend_Dojo_Form_Element_DateTextBox',
			'Editor'							=> 'Zend_Dojo_Form_Element_Editor',
			'FilteringSelect'					=> 'Zend_Dojo_Form_Element_FilteringSelect',
			'HorizontalSlider'					=> 'Zend_Dojo_Form_Element_HorizontalSlider',
			'NumberSpinner'						=> 'Zend_Dojo_Form_Element_NumberSpinner',
			'NumberTextBox'						=> 'Zend_Dojo_Form_Element_NumberTextBox',
			'PasswordTextBox'					=> 'Zend_Dojo_Form_Element_PasswordTextBox',
			'RadioButton'						=> 'Zend_Dojo_Form_Element_RadioButton',
			'SimpleTextarea'					=> 'Zend_Dojo_Form_Element_SimpleTextarea',
			'Slider'							=> 'Zend_Dojo_Form_Element_Slider',
			'SubmitButton'						=> 'Zend_Dojo_Form_Element_SubmitButton',
			'Textarea'							=> 'Zend_Dojo_Form_Element_Textarea',
			'TextBox'							=> 'Zend_Dojo_Form_Element_TextBox',
			'TimeTextBox'						=> 'Zend_Dojo_Form_Element_TimeTextBox',
			'ValidationTextBox'					=> 'Zend_Dojo_Form_Element_ValidationTextBox',
			'VerticalSlider'					=> 'Zend_Dojo_Form_Element_VerticalSlider',

			// Add Symbic Dojo Elements
			'FlexNumberTextBox'					=> 'Symbic_Dojo_Form_Element_FlexNumberTextBox',

			// REPLACE NONE-DOJO ELEMENTS !!!
			'Submit' 							=> 'Zend_Dojo_Form_Element_SubmitButton',
			'Text'								=> 'Zend_Dojo_Form_Element_ValidationTextBox',
			'Textarea'							=> 'Zend_Dojo_Form_Element_SimpleTextarea',
			'Select'							=> 'Zend_Dojo_Form_Element_FilteringSelect',
			'Password'							=> 'Zend_Dojo_Form_Element_PasswordTextBox',
		);

		foreach ($elements as $name => $className)
		{
			$this->setMap($name, $className);
		}
	}
}