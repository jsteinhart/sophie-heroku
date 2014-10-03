<?php
namespace Expadmin\Form\Participant\Codes\Printing;

class Snippets extends \Symbic_Form_Standard
{
    public function init()
    {
        $this->setLegend('Print Participant Code Snippets');

        $this->addElement('hidden', 'sessionId');

        $this->addElement('select', 'printer', array(
            'label' => 'Printer',
            'multiOptions' => $this->getDefaultPrinterOptions(), 'required' => true));

        $this->addElement('text', 'headline', array(
            'label' => 'Headline',
            'required' => false,
            'value' => $this->getDefaultHeadlineValue()
        ));

        $this->addElement('select', 'locale', array(
            'label' => 'Locale',
            'multiOptions' => $this->getLocaleOptions(), 'required' => true));

        /*        $this->addElement('MultiSelect', 'codes', array (
                    'label' => 'Select Codes',
                    'multiOptions' =>  $this->getCodeOptions(), 'required' => false)); */

		$this->addElement('select', 'order', array(
            'label' => 'Order',
            'required' => true,
            'multiOptions' => array('codeAsc'=>'Code Ascending', 'rand'=>'Random')
        ));
					
        $this->addElement('submit', 'print', array(
            'label' => 'Print', 'ignored' => true));
    }

    public function setPrinterOptions($options)
    {
        $this->getElement('printer')->setMultiOptions($options);
    }

    public function getDefaultPrinterOptions()
    {
        return array();
    }

    public function setHeadlineValue($value)
    {
        $this->getElement('headline')->setValue($value);
    }

    public function getDefaultHeadlineValue()
    {
        return '';
    }

    public function getLocaleOptions()
    {
        return array(
            'de_DE' => 'German (Germany)',
            //  'en_US' => 'English (US)'
        );
    }

    /*    public function getCodeOptions()
        {
            return array(
            );
        } */
}