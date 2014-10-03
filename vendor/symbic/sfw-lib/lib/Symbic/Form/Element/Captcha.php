<?php
class Symbic_Form_Element_Captcha extends Symbic_Form_Element_AbstractElement
{
	public $helper = 'formCaptcha';
	protected $_phrase = null;

	public function generatePhrase()
	{
		$phraseBuilder = new Gregwar\Captcha\PhraseBuilder();
		return $phraseBuilder->build();
	}

	public function setPhrase($phrase)
	{
		$this->_phrase = $phrase;
		return $this;
	}

	public function getPhrase()
	{
		return $this->_phrase;
	}
	
    public function isValid($value)
    {
		$phrase = $this->getPhrase();
		if ($phrase === $value)
		{
			return true;
		}
		else
		{
			return false;
		}
    }
}