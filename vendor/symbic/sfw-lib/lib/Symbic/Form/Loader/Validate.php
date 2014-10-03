<?php
class Symbic_Form_Loader_Validate extends Symbic_Loader_AliasMap
{
	protected $_filter = 'ucfirst';

	protected $_map = array(
		'Alnum'							=> 'Zend_Validate_Alnum',
		'Alpha'							=> 'Zend_Validate_Alpha',
		'Barcode'						=> 'Zend_Validate_Barcode',
		'Between'						=> 'Zend_Validate_Between',
		'Callback'						=> 'Zend_Validate_Callback',
		'Ccnum'							=> 'Zend_Validate_Ccnum',
		'CreditCard'					=> 'Zend_Validate_CreditCard',
		'Date'							=> 'Zend_Validate_Date',
		'Digits'						=> 'Zend_Validate_Digits',
		'DivisableBy'					=> 'Symbic_Validate_DivisableBy',
		'EmailAddress'					=> 'Zend_Validate_EmailAddress',
		'Float'							=> 'Zend_Validate_Float',
		'GreaterThan'					=> 'Zend_Validate_GreaterThan',
		'GreaterOrEqual'				=> 'Symbic_Validate_GreaterOrEqual',
		'Hex'							=> 'Zend_Validate_Hex',
		'Hostname'						=> 'Zend_Validate_Hostname',
		'Iban'							=> 'Zend_Validate_Iban',
		'Identical'						=> 'Zend_Validate_Identical',
		'InArray'						=> 'Zend_Validate_InArray',
		'Int'							=> 'Zend_Validate_Int',
		'Ip'							=> 'Zend_Validate_Ip',
		'Isbn'							=> 'Zend_Validate_Isbn',
		'LessThan'						=> 'Zend_Validate_LessThan',
		'LessOrEqual'					=> 'Symbic_Validate_LessOrEqual',
		'NotEmpty'						=> 'Zend_Validate_NotEmpty',
		'PostCode'						=> 'Zend_Validate_PostCode',
		'Regex'							=> 'Zend_Validate_Regex',
		'StringLength'					=> 'Zend_Validate_StringLength'
	);
}