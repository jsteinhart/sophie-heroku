<?php
class Sophie_Steptype_Quiz_Abstract_1_0_0_Steptype extends Sophie_Steptype_Abstract
{

	public $__quizAnswered = false;
	public $__form;

	private $__answerVar = null;
	private $__hideFormVar = null;
	private $__showResultVar = null;

	public function __construct($parameters = null )
	{
		parent::__construct($parameters);
		$this->translatePaths[] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'translate';
	}

	protected function getSteptypeAttributeConfigurations()
	{
		$config = parent :: getSteptypeAttributeConfigurations();
		$config['contentHeadline'] = array(
			'group' => 'Content',
			'title' => 'Headline',
		);
		$config['quizQuestion'] = array(
			'group' => 'Quiz',
			'title' => 'Question',
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

	private function initVarNames()
	{
		if (!$this->getContext())
		{
			throw new Exception('Cannot set var names');
			return false;
		}
		$stepId = $this->getContext()->getStepId();
		$this->__answerVar = '__' . $stepId . '_answer';
		$this->__hideFormVar = '__' . $stepId . '_hide_form';
		$this->__showResultVar = '__' . $stepId . '_show_result';
	}

	public function getForm()
	{
		$view = $this->getView();

		$form = new Symbic_Form_Standard();
		$form->setAction($this->getFrontUrl());
		$form->setMethod('POST');

		$stepId = $form->createElement('hidden', 'contextChecksum');
		$stepId->setValue($this->getContext()->getChecksum());
		$stepId->clearDecorators()->addDecorator('ViewHelper')->addDecorator('Errors');

		$form->addElements(array (
			$stepId
		));
		return $form;
	}

	public function isAnswerCorrect($answer)
	{
		return true;
	}

	public function render()
	{
		$this->initVarNames();

		$view = $this->getView();
		$translate = $this->getTranslate();
		$stepRender = $this->getStepRenderer();

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

			$answer = $this->getContext()->getApi('variable')->getPSL($this->__answerVar);
			$isAnswerCorrect = $this->isAnswerCorrect($answer);

			$showResult = $this->getContext()->getApi('variable')->getPSL($this->__showResultVar);
			$correctResponseRequired = !$this->getAttributeRuntimeValue('quizCorrectResponseRequired');

			$showQuizForm = true;

			if ($showResult || $isAnswerCorrect)
			{

				$content .= '<div class="quizResponseContainer">';

					$content .= '<div class="quizResponseInfo">';

						//second or more times
						if ($isAnswerCorrect)
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

					if ($isAnswerCorrect || $correctResponseRequired)
					{
						$showQuizForm = false;

						$content .= '<div class="quizResponseForm">';

						$content .= '<form action="' . $this->getFrontUrl() . '" method="POST" name="stepaction">';
							$content .= $view->formHidden('contextChecksum', $this->getContext()->getChecksum());
							$content .= '<input name="NextStep" type="submit" value="' . $translate->_('Continue ...') . '">';
						$content .= '</form>';

						$content .= '</div>';
					}

				$content .= '</div>';

			}

			if ($showQuizForm)
			{
				$content .= '<div id="quizForm">';
					$content .= $this->getForm()->render();
				$content .= '</div>';
			}

		$content .= '</div>';
		return $content;
	}

	public function process()
	{
		$this->initVarNames();
		$variableApi = $this->getContext()->getApi('variable');

		$answer = $this->getController()->getRequest()->getParam('answer', null);
		if (!is_null($answer))
		{
			$form = $this->getForm();
			if ($this->getController()->getRequest()->isPost() && $form->isValid($_POST))
			{
				$values = $form->getValues();
				$variableApi->setPSL($this->__answerVar, $values['answer']);
				$variableApi->setPSL($this->__showResultVar, true);
			}
			return false; // stay in the step
		}
		$variableApi->setPSL($this->__showResultVar, false);

		if (!$this->getAttributeRuntimeValue('quizCorrectResponseRequired'))
		{
			$processApi = $this->getContext()->getApi('process')->transferParticipantToNextStep();
			return true;
		}

		$answer = $this->getContext()->getApi('variable')->getPSL($this->__answerVar);
		if ($this->isAnswerCorrect($answer))
		{
			$processApi = $this->getContext()->getApi('process')->transferParticipantToNextStep();
			return true;
		}

		return false;
	}


	///////////////////////////////////////////////////////////////////

	public function adminSetDefaultValues()
	{
		$this->setAttributeValue('quizCorrectResponseRequired', '0');
		$this->setAttributeValue('quizMessageCorrect', 'Your answer is right.');
		$this->setAttributeValue('quizMessageIncorrect', 'Your answer is wrong.');
		$this->setAttributeValue('quizMessageEmpty', 'Please give an answer.');
	}

	public function adminGetTabs()
	{
		$tabs = parent::adminGetTabs();
		$tabs[] = array('id'=>'quiz', 'title'=>'Quiz', 'order'=>100);
		$tabs[] = array('id'=>'response', 'title'=>'Response', 'order'=>300);
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
			'label' => 'Quiz Content',
			'trim' => 'true',
			'required' => false,
			'order' => $order
		), array ());
		$quizQuestion->setValue($this->getAttributeValue('quizQuestion'));
		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'quiz-ContentPane\'), \'onShow\', function() { ' . $quizQuestion->getJsInstance() . '.refresh(); } );');

		$order += 100;
		$submit = $subForm->createElement('submit', 'quizSave', array (
			'label' => 'Save',
			'order' => $order,
			'ignore' => 'true'
		));
		$subForm->addElements(array (
			$contentHeadline,
			$quizQuestion,
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
		$this->setAttributeValue('quizQuestion', $parameters['quizQuestion']);
	}

	public function adminResponseTabProcess($parameters)
	{
		$this->setAttributeValue('quizCorrectResponseRequired', $parameters['quizCorrectResponseRequired']);
		$this->setAttributeValue('quizMessageCorrect', $parameters['quizMessageCorrect']);
		$this->setAttributeValue('quizMessageIncorrect', $parameters['quizMessageIncorrect']);
		$this->setAttributeValue('quizMessageEmpty', $parameters['quizMessageEmpty']);
	}

}