<?php
namespace Expadmin\Form\Options;

class Timer extends \Symbic_Form_Standard
{

	public function init()
	{
		$this->setLegend('Set Timer');

		$this->addElement('text', 'timerDuration', array (
			'label' => 'Timer Duration',
			'required' => true,
			'regExp' => '\d{1,}:\d{2}',
			'invalidMessage' => 'Invalid time. Format: Minutes:Seconds.',
			'promptMessage' => 'Format: Minutes:Seconds.',
		), array ());

		$this->addElement('text', 'timerCountdownDuration', array (
			'label' => 'Countdown Duration / Offset',
			'required' => true,
			'regExp' => '\d{1,}:\d{2}',
			'invalidMessage' => 'Invalid time. Format: Minutes:Seconds.',
			'promptMessage' => 'Even when the countdown is disabled a pre-timer offset (at least 0:03) should be given to synchronize all participants. Format: Minutes:Seconds.',
		), array ());

		$this->addElement('submit', 'submit', array (
			'label' => 'Start Countdown'
		));
	}

}