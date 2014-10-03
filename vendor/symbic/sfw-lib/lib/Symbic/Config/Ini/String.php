<?php
class Symbic_Config_Ini_String extends Zend_Config_Ini
{
    protected $_loadFileErrorStr;
    protected function _parseIniFile($string) {
        set_error_handler(array($this, '_loadFileErrorHandler'));
        $iniArray = parse_ini_string($string, true); // Warnings and errors are suppressed
        restore_error_handler();

        // Check if there was a error while loading file
        if ($this->_loadFileErrorStr !== null) {
            throw new Zend_Config_Exception($this->_loadFileErrorStr);
        }
        return $iniArray;
    }
}