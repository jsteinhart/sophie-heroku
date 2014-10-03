<?php
class Symbic_Form_Element_Checkbox extends Symbic_Form_Element_AbstractElement
{
	public $checked = false;
	public $helper = 'formCheckbox';
	public $options = array(
        'checkedValue'   => '1',
        'uncheckedValue' => '0',
    );
    protected $_checkedValue = '1';
    protected $_uncheckedValue = '0';
    protected $_value = '0';

    public function setOptions(array $options)
    {
        if (array_key_exists('checkedValue', $options)) {
            $this->setCheckedValue($options['checkedValue']);
            unset($options['checkedValue']);
        }
        if (array_key_exists('uncheckedValue', $options)) {
            $this->setUncheckedValue($options['uncheckedValue']);
            unset($options['uncheckedValue']);
        }
        parent::setOptions($options);

        $curValue = $this->getValue();
        $test     = array($this->getCheckedValue(), $this->getUncheckedValue());
        if (!in_array($curValue, $test)) {
            $this->setValue($curValue);
        }

        return $this;
    }

    public function setValue($value)
    {
        if ($value == $this->getCheckedValue()) {
            parent::setValue($value);
            $this->checked = true;
        } else {
            parent::setValue($this->getUncheckedValue());
            $this->checked = false;
        }
        return $this;
    }

    public function setCheckedValue($value)
    {
        $this->_checkedValue = (string) $value;
        $this->options['checkedValue'] = $value;
        return $this;
    }

    public function getCheckedValue()
    {
        return $this->_checkedValue;
    }

    public function setUncheckedValue($value)
    {
        $this->_uncheckedValue = (string) $value;
        $this->options['uncheckedValue'] = $value;
        return $this;
    }

    public function getUncheckedValue()
    {
        return $this->_uncheckedValue;
    }

    public function setChecked($flag)
    {
        $this->checked = (bool) $flag;
        if ($this->checked) {
            $this->setValue($this->getCheckedValue());
        } else {
            $this->setValue($this->getUncheckedValue());
        }
        return $this;
    }

    public function isChecked()
    {
        return $this->checked;
    }
}