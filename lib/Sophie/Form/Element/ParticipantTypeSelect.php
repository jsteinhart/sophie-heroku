<?php
class Sophie_Form_Element_ParticipantTypeSelect extends Symbic_Form_Element_Multiselect
{
	public $helper = 'formParticipantTypeSelect';

	public function getValue()
	{
		$value = parent :: getValue();
		if (is_array($value))
		{
			$options = $this->getMultiOptions();
			$selectAll = true;
			if (!in_array('__all_' . md5(print_r($options, true)), $value))
			{
				foreach ($options as $option => $title)
				{
					if (!in_array($option, $value))
					{
						$selectAll = false;
						break;
					}
				}
			}
			if ($selectAll)
			{
				$value = array();
			}
		}
		return $value;
	}
}
