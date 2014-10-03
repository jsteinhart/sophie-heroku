<?php
class Sophie_Steptype_Quiz_Choice_1_0_0_Steptype extends Sophie_Steptype_Abstract
{

	public $__quizAnswered = false;
	public $__form;
	public $__answers;

	public function __construct($parameters = null )
	{
		parent::__construct($parameters);
		$this->translatePaths[] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'translate';
	}

	protected function getSteptypeAttributeConfigurations()
	{
		$config = parent :: getSteptypeAttributeConfigurations();
		$config['quizQuestion'] = array(
			'group' => 'Quiz',
			'title' => 'Question',
		);
		$config['quizAnswersJson'] = array(
			'group' => 'Quiz',
			'title' => 'Answers Json',
		);
		$config['quizCorrectAnswer'] = array(
			'group' => 'Quiz',
			'title' => 'Correct Answer',
		);
		$config['quizCorrectResponseRequired'] = array(
			'group' => 'Quiz',
			'title' => 'Correct Response Required',
		);
		$config['quizMessageCorrect'] = array(
			'group' => 'Quiz',
			'title' => 'Message on Correct Answer',
		);
		$config['quizMessageIncorrect'] = array(
			'group' => 'Quiz',
			'title' => 'Message on Incorrect Answer',
		);
		$config['quizMessageEmpty'] = array(
			'group' => 'Quiz',
			'title' => 'Message on Empty',
		);
		return $config;
	}

	public function getQuizAnswers()
	{
		$quizAnswersJson = $this->getAttributeRuntimeValue('quizAnswersJson');
		if (!empty ($quizAnswersJson))
		{
			$quizAnswers = Zend_Json :: decode($quizAnswersJson, true);
		}
		else
		{
			$quizAnswers = array();
		}
		return $quizAnswers;
	}

	public function getQuizAnswersText()
	{
		$quizAnswers = $this->getQuizAnswers();
		$quizAnswersText = '';
		foreach ($quizAnswers as $quizAnswersListKey => $quizAnswersListValue)
		{
			$quizAnswersText .= $quizAnswersListKey . ':' . $quizAnswersListValue . "\n";
		}
		return $quizAnswersText;
	}

	public function parseQuizAnswersText($text)
	{
		$text = trim($text);
		$quizAnswersArray1 = explode("\n", $text);
		$quizAnswersArray2 = array ();
		foreach ($quizAnswersArray1 as $quizAnswers2)
		{
			$quizAnswers2 = trim($quizAnswers2);
			$quizAnswers3 = explode(":", $quizAnswers2, 2);
			if (!isset($quizAnswers3[1]))
			{
				$quizAnswers3[1] = '';
			}

			$key = trim($quizAnswers3[0]);
			$value = trim($quizAnswers3[1]);
			if (empty($key) && empty($value))
			{
				// remove empty options
				continue;
			}
			$quizAnswersArray2[$key] = $value;
		}
		return $quizAnswersArray2;
	}

	public function adminSetDefaultValues()
	{
		$translate = $this->getTranslate();
		$this->setAttributeValue('quizAnswersJson', '{"a":"Option A","b":"Option B"}');
		$this->setAttributeValue('quizQuestion', 'Will it be a or b?');
		$this->setAttributeValue('quizCorrectAnswer', 'a');
		$this->setAttributeValue('quizMessageCorrect', $translate->_('The given answer is correct.'));
		$this->setAttributeValue('quizMessageIncorrect', $translate->_('The given answer is not correct.'));
		$this->setAttributeValue('quizMessageEmpty', $translate->_('Please select one of the answers.'));
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

		$form = new Symbic_Form_Standard();
		$form->addPrefixPath('Symbic_Form_Decorator', 'Symbic/Form/Decorator', 'decorator');
		$form->addPrefixPath('Symbic_Form_Element', 'Symbic/Form/Element', 'element');
		$form->addElementPrefixPath('Symbic_Form_Decorator', 'Symbic/Form/Decorator', 'decorator');
		$form->addDisplayGroupPrefixPath('Symbic_Form_Decorator', 'Symbic/Form/Decorator');

		$form->setAction($this->getFrontUrl());
		$form->setMethod('POST');

		$stepId = $form->createElement('hidden', 'contextChecksum');
		$stepId->setValue($this->getContext()->getChecksum());
		$stepId->clearDecorators()->addDecorator('ViewHelper')->addDecorator('Errors');

		$answer = $form->createElement('radioList', 'answer', array(
			'multiOptions' => $this->getQuizAnswers(),
			'escape' => false,
			'required' => true
		));

		$emptyMessage = $this->getAttributeRuntimeValue('quizMessageEmpty');
		if (!empty($emptyMessage))
		{
			$answer->addValidator('NotEmpty', true, array (
				'messages' => array (
					'isEmpty' => $emptyMessage
				)
			));
		}

		$submit = $form->createElement('submit', 'submit')->setLabel($translate->_('Submit ...'));

		$form->addElements(array (
			$stepId,
			$answer,
			$submit
		));

		$this->__form = $form;
		return $this->__form;
	}

	public function isAnswerCorrect($answer)
	{
		return (!is_null($answer) && $this->getAttributeRuntimeValue('quizCorrectAnswer') == $answer);
	}

	public function render()
	{
		$view = $this->getView();
		$translate = $this->getTranslate();
		$stepRender = $this->getStepRenderer();
		$variableApi = $this->getContext()->getApi('variable');
		$answerVariable = $variableApi->getPSL('__' . $this->getContext()->getStepId() . '_answer');
		$answerCorrect = $this->isAnswerCorrect($answerVariable);
		$requireCorrect = $this->getAttributeRuntimeValue('quizCorrectResponseRequired');

		// assemble content
		$contentHeadline = $stepRender->render($this->getAttributeRuntimeValue('contentHeadline'));
		$contentBody = $stepRender->render($this->getAttributeRuntimeValue('contentBody'));
		$quizQuestion = $stepRender->render($this->getAttributeRuntimeValue('quizQuestion'));

		if ($contentHeadline != '' || $contentBody != '' || $quizQuestion != '')
		{
			$content = '<div id="cheader">';

				if ($contentHeadline != '')
				{
					$content .= '<div class="cheadline">' . $contentHeadline . '</div>';
				}

				$content .= '<div class="cheadtext">';
				if ($contentBody != '')
				{
					$content .= $contentBody;
				}
				$content .= '</div>';

				$content .= '<div class="cheadtext">';
				if ($quizQuestion != '')
				{
					$content .= $quizQuestion;
				}
				$content .= '</div>';

			$content .= '</div>';
		}

		$content .= '<div id="caction">';
			// determine at what stage we are

		if (!is_null($answerVariable))
		{

			$answers = $this->getQuizAnswers();

			$content .= '<div class="quizResponseContainer">';
			$content .= '<div class="quizResponseInfo">';

			$content .= '<div class="quizResponse">';
				$content .= $translate->_('Your answer') . ': ' . $answers[$answerVariable];
			$content .= '</div>';

			//second or more times
			if ($answerCorrect)
			{
				// correct answer given
				$quizMessageCorrect = $stepRender->render($this->getAttributeRuntimeValue('quizMessageCorrect'));
				if ($quizMessageCorrect != '')
				{
					$content .= '<div class="quizMessage">';
						$content .= '<div class="quizMessageCorrect">';
							$content .= $quizMessageCorrect;
						$content .= '</div>';
					$content .= '</div>';
				}
			}
			else
			{
				// incorrect answer given
				$quizMessageIncorrect = $stepRender->render($this->getAttributeRuntimeValue('quizMessageIncorrect'));
				if ($quizMessageIncorrect != '')
				{
					$content .= '<div class="quizMessage">';
						$content .= '<div class="quizMessageIncorrect">';
							$content .= $quizMessageIncorrect;
						$content .= '</div>';
					$content .= '</div>';
				}
			}

			$content .= '</div>';
			$content .= '</div>';
		}

		$form = $this->getForm();
		// Check if the quiz was finished:
		if (!is_null($answerVariable) && ($answerCorrect || !$requireCorrect))
		{
			// disable radio elements:
			$form->getElement('answer')->setAttrib('disabled', 'disabled');

			// reset submit button's value:
			$form->getElement('submit')->setLabel($translate->_('Continue ...'));
		}

		if (true || is_null($answerVariable) || (!$answerCorrect && $requireCorrect))
		{
			$content .= '<div id="quizForm">';
			$content .= $form->render();
			$content .= '</div>';
		}
		else
		{
			$content .= '<div class="quizResponseForm">';
			$content .= '<form action="' . $this->getFrontUrl() . '" method="POST" name="stepaction">';
			$content .= $view->formHidden('contextChecksum', $this->getContext()->getChecksum());
			$content .= '<input name="NextStep" type="submit" value="' . $translate->_('Submit ...') . '">';
			$content .= '</form>';
			$content .= '</div>';
		}

		return $content;
	}

	public function process()
	{
		$variableApi = $this->getContext()->getApi('variable');
		$stepId = $this->getContext()->getStepId() ;
		$answerVariable = $variableApi->getPSL('__' . $stepId. '_answer');

		if (!is_null($answerVariable))
		{
			$requireCorrect = $this->getAttributeRuntimeValue('quizCorrectResponseRequired');
			$answerCorrect = $this->isAnswerCorrect($answerVariable);
			if (!$requireCorrect || $answerCorrect)
			{
				return parent::process();
			}
		}

		$form = $this->getForm();
		if ($this->getController()->getRequest()->isPost() && $form->isValid($_POST))
		{
			$values = $form->getValues();
			$variableApi->setPSL('__' . $stepId . '_answer', $values['answer']);
		}
		return false;
	}


	///////////////////////////////////////////////////////////////////

	public function adminGetTabs()
	{
		$tabs = parent :: adminGetTabs();
		$tabs[] = array (
			'id' => 'quiz',
			'title' => 'Quiz',
			'order' => 100
		);
		$tabs[] = array (
			'id' => 'response',
			'title' => 'Response',
			'order' => 200
		);
		return $tabs;
	}

	public function adminQuizTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subForm = $form->getSubForm('quiz');
		if (is_null($subForm))
		{
			$subForm = $form->createSubForm();
			$subForm->setAttribs(array (
				'legend' => 'Quiz',
				'dijitParams' => array (
					'title' => 'Quiz'
				)
			));
			$form->addSubForm($subForm, 'quiz');
		}

		$order = 100;
		$contentHeadline = $subForm->createElement('TextInput', 'contentHeadline', array (
			'label' => 'Headline',
			'trim' => 'true',
			'order' => $order
		), array ());
		$contentHeadline->setValue($this->getAttributeValue('contentHeadline'));

		$order += 100;
		$quizQuestion = $subForm->createElement('SwitchCodemirrorWysiwygTextarea', 'quizQuestion', array (
			'label' => 'Question',
			'trim' => 'true',
			'required' => true,
			'order' => $order
		), array ());
		$quizQuestion->setValue($this->getAttributeValue('quizQuestion'));
		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'quiz-ContentPane\'), \'onShow\', function() { ' . $quizQuestion->getJsInstance() . '.refresh(); } );');

		$order += 100;
		$quizAnswers = $subForm->createElement('Textarea', 'quizAnswers', array (
			'label' => 'Possible Answers',
			'trim' => 'true',
			'required' => true,
			'order' => $order
		), array ());
		$quizAnswers->setValue($this->getQuizAnswersText());

		$order += 100;
		$quizCorrectAnswer = $subForm->createElement('TextInput', 'quizCorrectAnswer', array (
			'label' => 'Correct Answer',
			'trim' => 'true',
			'required' => true,
			'order' => $order
		), array ());
		$quizCorrectAnswer->setValue($this->getAttributeValue('quizCorrectAnswer'));

		$order += 100;
		$submit = $subForm->createElement('submit', 'quizSave', array (
			'label' => 'Save',
			'order' => $order,
			'ignore' => 'true'
		));
		$subForm->addElements(array (
			$contentHeadline,
			$quizQuestion,
			$quizAnswers,
			$quizCorrectAnswer,
			$submit
		));
	}

	public function adminResponseTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subForm = $form->getSubForm('response');
		if (is_null($subForm))
		{
			$subForm = $form->createSubForm();
			$subForm->setAttribs(array (
				'legend' => 'Response',
				'dijitParams' => array (
					'title' => 'Response'
				)
			));
			$form->addSubForm($subForm, 'response');
		}

		$order = 0;

		$order += 100;
		$quizCorrectResponseRequiredOptions = array (
			'0' => 'No, continue on wrong response.',
			'1' => 'Yes, force participant to answer until (s)he responds correctly.'
		);
		$quizCorrectResponseRequired = $subForm->createElement('Select', 'quizCorrectResponseRequired', array (
			'multiOptions' => $quizCorrectResponseRequiredOptions,
			'label' => 'Require correct response',
			'required' => true,
			'order' => $order
		));
		$quizCorrectResponseRequired->setValue($this->getAttributeValue('quizCorrectResponseRequired'));

		$order += 100;
		$quizMessageCorrect = $subForm->createElement('SwitchCodemirrorWysiwygTextarea', 'quizMessageCorrect', array (
			'label' => 'Message on correct answer',
			'trim' => 'true',
			'order' => $order
		), array ());
		$quizMessageCorrect->setValue($this->getAttributeValue('quizMessageCorrect'));
		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'response-ContentPane\'), \'onShow\', function() { ' . $quizMessageCorrect->getJsInstance() . '.refresh(); } );');

		$order += 100;
		$quizMessageIncorrect = $subForm->createElement('SwitchCodemirrorWysiwygTextarea', 'quizMessageIncorrect', array (
			'label' => 'Message on incorrect answer',
			'trim' => 'true',
			'order' => $order
		), array ());
		$quizMessageIncorrect->setValue($this->getAttributeValue('quizMessageIncorrect'));
		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'response-ContentPane\'), \'onShow\', function() { ' . $quizMessageIncorrect->getJsInstance() . '.refresh(); } );');

		$order += 100;
		$quizMessageEmpty = $subForm->createElement('TextInput', 'quizMessageEmpty', array (
			'label' => 'Message on empty answer',
			'trim' => 'true',
			'required' => true,
			'order' => $order
		), array ());
		$quizMessageEmpty->setValue($this->getAttributeValue('quizMessageEmpty'));

		$order += 100;
		$submit = $subForm->createElement('submit', 'responseSave', array (
			'label' => 'Save',
			'order' => $order,
			'ignore' => 'true'
		));
		$subForm->addElements(array (
			$quizCorrectResponseRequired,
			$quizMessageCorrect,
			$quizMessageIncorrect,
			$quizMessageEmpty,
			$submit
		));
	}

	public function adminQuizTabProcess($parameters)
	{
		$this->setAttributeValue('contentHeadline', $parameters['contentHeadline']);
		$this->setAttributeValue('quizQuestion', $this->getController()->getRequest()->getParam('quizQuestion', ''));
		$quizAnswers = $this->parseQuizAnswersText($parameters['quizAnswers']);
		$this->setAttributeValue('quizAnswersJson', Zend_Json::encode($quizAnswers));
		$this->setAttributeValue('quizCorrectAnswer', $this->getController()->getRequest()->getParam('quizCorrectAnswer', ''));
	}

	public function adminResponseTabProcess($parameters)
	{
		$this->setAttributeValue('quizCorrectResponseRequired', $this->getController()->getRequest()->getParam('quizCorrectResponseRequired', ''));
		$this->setAttributeValue('quizMessageCorrect', $this->getController()->getRequest()->getParam('quizMessageCorrect', ''));
		$this->setAttributeValue('quizMessageIncorrect', $this->getController()->getRequest()->getParam('quizMessageIncorrect', ''));
		$this->setAttributeValue('quizMessageEmpty', $this->getController()->getRequest()->getParam('quizMessageEmpty', ''));
	}

}