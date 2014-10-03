<?php
$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
$steptypeFactory->load('Sophie_Steptype_Quiz_Input_1_0_0');

class Sophie_Steptype_Quiz_Input_Multivalue_1_0_0_Steptype extends Sophie_Steptype_Quiz_Input_1_0_0_Steptype
{	

	public function isAnswerCorrect($answer)
	{
		for ($no = 1; $no <= $this->questionNumber; $no++)
		{
			if (!isset($answer[$no]))
			{
				return false;
			}

			$correctAnswers = $this->getAttributeRuntimeValue('correctAnswer' . $no);
			$correctAnswers = split("\n", $correctAnswers);

			array_walk($correctAnswers, function(&$value, $key){
				$value = trim($value);
			});

			$valueFound = false;
			foreach ($correctAnswers as $correctAnswer)
			{
				if ($answer[$no] === $correctAnswer)
				{
					$valueFound = true;
				}
			}
			if (!$valueFound)
			{
				return false;
			}
		}
		return true;
	}


	///////////////////////////////////////////////////////////////////

	public function adminSetDefaultValues()
	{
		parent::adminSetDefaultValues();
		for ($no = 1; $no <= $this->questionNumber; $no++)
		{
			$answer = chr($no + 64) . "\n" . chr($no + 65);
			$this->setAttributeValue('correctAnswer' . $no, $answer);
		}
	}

	public function adminQuestionTabInit()
	{
		parent::adminQuestionTabInit();

		$view = $this->getView();
		$form = $this->adminGetForm();
		$subForm = $form->getSubForm('questions');

		for ($no = 1; $no <= $this->questionNumber; $no++)
		{
			$labelAppend = ($this->questionNumber == 1) ? '' : (' (' . $no . ')');
			$correctAnswerOld = $subForm->createElement('textarea', 'correctAnswer' . $no);
			
			$correctAnswer = $subForm->createElement('textarea', 'correctAnswer' . $no, array (
				'label' => 'Correct Answer' . $labelAppend,
				'trim' => 'true',
				'required' => true,
				'order' => $correctAnswerOld->getOrder()
			), array ());
			$correctAnswer->setValue($this->getAttributeValue('correctAnswer' . $no));
			
			$subForm->addElement($correctAnswer);
		}
	}

}