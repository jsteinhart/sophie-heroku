<?php
class Symbic_Form_Loader_Filter extends Symbic_Loader_AliasMap
{
	protected $_filter = 'ucfirst';

	protected $_map = array(
		'Alnum'						=> 'Zend_Filter_Alnum',
		'Alpha'						=> 'Zend_Filter_Alpha',
		'BaseName'					=> 'Zend_Filter_BaseName',
		'Boolean'					=> 'Zend_Filter_Boolean',
		'Callback'					=> 'Zend_Filter_Callback',
		'Compress'					=> 'Zend_Filter_Compress',
		'Decompress'				=> 'Zend_Filter_Decompress',
		'Decrypt'					=> 'Zend_Filter_Decrypt',
		'Digits'					=> 'Zend_Filter_Digits',
		'Dir'						=> 'Zend_Filter_Dir',
		'Encrypt'					=> 'Zend_Filter_Encrypt',
		'HtmlEntities'				=> 'Zend_Filter_HtmlEntities',
		'Int'						=> 'Zend_Filter_Int',
		'LocalizedToNormalized'		=> 'Zend_Filter_LocalizedToNormalized',
		'NormalizedToLocalized'		=> 'Zend_Filter_NormalizedToLocalized',
		'Null'						=> 'Zend_Filter_Null',
		'PregReplace'				=> 'Zend_Filter_PregReplace',
		'RealPath'					=> 'Zend_Filter_RealPath',
		'StringToLower'				=> 'Zend_Filter_StringToLower',
		'StringToUpper'				=> 'Zend_Filter_StringToUpper',
		'StringTrim'				=> 'Zend_Filter_StringTrim',
		'StripNewlines'				=> 'Zend_Filter_StripNewlines',
		'StripTags'					=> 'Zend_Filter_StripTags'
	);
}