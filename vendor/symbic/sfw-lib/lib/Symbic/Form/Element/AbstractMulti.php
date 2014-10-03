<?php
abstract class Symbic_Form_Element_AbstractMulti extends Symbic_Form_Element_AbstractElement
{
    public $options = array();
    protected $_registerInArrayValidator = true;
    protected $_separator = '<br />';
    protected $_translated = array();

    public function getSeparator()
    {
        return $this->_separator;
    }

    public function setSeparator($separator)
    {
        $this->_separator = $separator;
        return $this;
    }

    protected function _getMultiOptions()
    {
        if (null === $this->options || !is_array($this->options)) {
            $this->options = array();
        }

        return $this->options;
    }

    public function addMultiOption($option, $value = '')
    {
        $option  = (string) $option;
        $this->_getMultiOptions();
        if (!$this->_translateOption($option, $value)) {
            $this->options[$option] = $value;
        }

        return $this;
    }

    public function addMultiOptions(array $options)
    {
        foreach ($options as $option => $value) {
            if (is_array($value)
                && array_key_exists('key', $value)
                && array_key_exists('value', $value)
            ) {
                $this->addMultiOption($value['key'], $value['value']);
            } else {
                $this->addMultiOption($option, $value);
            }
        }
        return $this;
    }

    public function setMultiOptions(array $options)
    {
        $this->clearMultiOptions();
        return $this->addMultiOptions($options);
    }

    public function getMultiOption($option)
    {
        $option  = (string) $option;
        $this->_getMultiOptions();
        if (isset($this->options[$option])) {
            $this->_translateOption($option, $this->options[$option]);
            return $this->options[$option];
        }

        return null;
    }

    public function getMultiOptions()
    {
        $this->_getMultiOptions();
        foreach ($this->options as $option => $value) {
            $this->_translateOption($option, $value);
        }
        return $this->options;
    }

    public function removeMultiOption($option)
    {
        $option  = (string) $option;
        $this->_getMultiOptions();
        if (isset($this->options[$option])) {
            unset($this->options[$option]);
            if (isset($this->_translated[$option])) {
                unset($this->_translated[$option]);
            }
            return true;
        }

        return false;
    }

    public function clearMultiOptions()
    {
        $this->options = array();
        $this->_translated = array();
        return $this;
    }

    public function setRegisterInArrayValidator($flag)
    {
        $this->_registerInArrayValidator = (bool) $flag;
        return $this;
    }

    public function registerInArrayValidator()
    {
        return $this->_registerInArrayValidator;
    }

    public function isValid($value, $context = null)
    {
        if ($this->registerInArrayValidator()) {
            if (!$this->getValidator('InArray')) {
                $multiOptions = $this->getMultiOptions();
                $options      = array();

                foreach ($multiOptions as $opt_value => $opt_label) {
                    // optgroup instead of option label
                    if (is_array($opt_label)) {
                        $options = array_merge($options, array_keys($opt_label));
                    }
                    else {
                        $options[] = $opt_value;
                    }
                }

                $this->addValidator(
                    'InArray',
                    true,
                    array($options)
                );
            }
        }
        return parent::isValid($value, $context);
    }

    protected function _translateOption($option, $value)
    {
        if ($this->translatorIsDisabled()) {
            return false;
        }

        if (!isset($this->_translated[$option]) && !empty($value)) {
            $this->options[$option] = $this->_translateValue($value);
            if ($this->options[$option] === $value) {
                return false;
            }
            $this->_translated[$option] = true;
            return true;
        }

        return false;
    }

    protected function _translateValue($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->_translateValue($val);
            }
            return $value;
        } else {
            if (null !== ($translator = $this->getTranslator())) {
                return $translator->translate($value);
            }

            return $value;
        }
    }
}