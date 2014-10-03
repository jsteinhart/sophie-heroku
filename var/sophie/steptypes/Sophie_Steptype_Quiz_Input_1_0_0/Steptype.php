<?php
$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
$steptypeFactory->load('Sophie_Steptype_Quiz_Abstract_1_0_0');

class Sophie_Steptype_Quiz_Input_1_0_0_Steptype extends Sophie_Steptype_Quiz_Abstract_1_0_0_Steptype
{
	public $questionNumber = 1;

	protected function getSteptypeAttributeConfigurations()
	{
		$config = parent :: getSteptypeAttributeConfigurations();
		for ($no = 1; $no <= $this->questionNumber; $no++)
		{
			$config['answerLabel' . $no] = array(
				'group' => 'Quiz',
				'title' => 'Answer Label ' . $no,
			);
			$config['correctAnswer' . $no] = array(
				'group' => 'Quiz',
				'title' => 'Correct Answer ' . $no,
			);
		}
		return $config;
	}
	
	public function getForm()
	{
		if (!is_null($this->__form))
		{
			return $this->__form;
		}

		$view = $this->getView();
		$translate = $this->getTranslate();
		$stepRender = $this->getStepRenderer();
		$context = $this->getContext();

		$form = parent::getForm();

		$emptyMessage = $this->getAttributeRuntimeValue('quizMessageEmpty');
		$savedAnswers = $context->getApi('variable')->getPSL('__' . $context->getStepId() . '_answer');
		if (empty($savedAnswers))
		{
			$savedAnswers = array();
		}

		$order = 100;

		$answers = array();
		for ($no = 1; $no <= $this->questionNumber; $no++)
		{
			$answers[$no] = $form->createElement('Text', (string)$no, array (
				'label' => $this->getAttributeValue('answerLabel' . $no),
				'trim' => 'true',
				'order' => $order + $no,
				'required' => true,
				'autoInsertNotEmptyValidator' => false
			), array ());
			if (isset($savedAnswers[$no]))
			{
				$answers[$no]->setValue($savedAnswers[$no]);
			}
			$answers[$no]->setBelongsTo('answer');
			if (!empty($emptyMessage))
			{
				$answers[$no]->addValidator('NotEmpty', true, array (
					'messages' => $emptyMessage
				));
			}
			$form->addElement($answers[$no]);
		}

		$order += 100;
		$submit = $form->createElement('submit', 'submit', array('order' => $order))->setLabel($translate->_('Submit ...'));

		$form->addElement($submit);

		$this->__form = $form;
		return $this->__form;
	}

	public function isAnswerCorrect($answer)
	{
		for ($no = 1; $no <= $this->questionNumber; $no++)
		{
			if (!isset($answer[$no]))
			{
				return false;
			}
			if ($this->getAttributeRuntimeValue('correctAnswer' . $no) != $answer[$no])
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
			$answer = chr($no + 64);
			$this->setAttributeValue('answerLabel' . $no, 'Please enter ' . $answer . '!');
			$this->setAttributeValue('correctAnswer' . $no, $answer);
		}
	}

	public function adminGetTabs()
	{
		$tabs = parent::adminGetTabs();
		$legend = ($this->questionNumber == 1) ? 'Question' : 'Questions';
		$tabs[] = array('id'=>'question', 'title'=>$legend, 'order'=>200);
		return $tabs;
	}

	public function adminQuestionTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subForm = $form->getSubForm('questions');
		if (is_null($subForm))
		{
			$legend = ($this->questionNumber == 1) ? 'Question' : 'Questions';
			$subForm = $form->createSubForm();
			$subForm->setAttribs(array (
				'legend' => $legend,
				'dijitParams' => array (
					'title' => $legend,
				)
			));
			$form->addSubForm($subForm, 'questions');
		}

		$order = 0;
		$elements = array();

		for ($no = 1; $no <= $this->questionNumber; $no++)
		{
			$labelAppend = ($this->questionNumber == 1) ? '' : (' (' . $no . ')');

			$order += 100;
			$answerLabel = $subForm->createElement('TextInput', 'answerLabel' . $no, array (
				'label' => 'Question / label for input box' . $labelAppend,
				'trim' => 'true',
				'order' => $order
			), array ());
			$answerLabel->setValue($this->getAttributeValue('answerLabel' . $no));
			$elements[] = $answerLabel;

			$order += 100;
			$correctAnswer = $subForm->createElement('TextInput', 'correctAnswer' . $no, array (
				'label' => 'Correct Answer' . $labelAppend,
				'trim' => 'true',
				'required' => true,
				'order' => $order
			), array ());
			$correctAnswer->setValue($this->getAttributeValue('correctAnswer' . $no));
			$elements[] = $correctAnswer;
		}
		$subForm->addElements($elements);

		$order += 100;
		$submit = $subForm->createElement('submit', 'answerSave', array (
			'label' => 'Save',
			'order' => $order,
			'ignore' => 'true'
		));

		$subForm->addElement($submit);
	}

	public function adminQuestionTabProcess($parameters)
	{
		for ($no = 1; $no <= $this->questionNumber; $no++)
		{
			$this->setAttributeValue('answerLabel' . $no, $parameters['answerLabel' . $no]);
			$this->setAttributeValue('correctAnswer' . $no, $parameters['correctAnswer' . $no]);
		}
	}

}