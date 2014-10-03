<?php
$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
$steptypeFactory->load('Sophie_Steptype_Info_1_0_0');

class Sophie_Steptype_Questionnaire_1_0_0_Steptype extends Sophie_Steptype_Info_1_0_0_Steptype
{
	private $_processMessage = array();
	private $_questions = null;

	public function __construct()
	{
		parent::__construct();
		$this->translatePaths[] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'translate';
	}

	protected function getSteptypeAttributeConfigurations()
	{
		$config = parent :: getSteptypeAttributeConfigurations();
		// TODO: add steptype attribute configuration
		return $config;
	}
	
	public function adminSetDefaultValues()
	{
		$this->setAttributeValue('contentHeadline', 'Please answer the following questions');
		$this->setAttributeValue('contentBody', '');

		$this->setAttributeValue('questPreStandard', '');

		$this->setAttributeValue('questGenderActivate', 1);
		$this->setAttributeValue('questGenderDescription', 'Gender');
		$this->setAttributeValue('questGenderFemale', 'Female');
		$this->setAttributeValue('questGenderMale', 'Male');
		$this->setAttributeValue('questGenderMessage', 'Please select the gender');
		$this->setAttributeValue('questGenderRequired', 1);
		$this->setAttributeValue('questGenderSpecified', '- Prefer not to say -');
		$this->setAttributeValue('questGenderVariable', 'gender');

		$this->setAttributeValue('questAgeActivate', 1);
		$this->setAttributeValue('questAgeDescription', 'Age');
		$this->setAttributeValue('questAgeYears', 'years');
		$this->setAttributeValue('questAgeMessage', 'Please type in your age');
		$this->setAttributeValue('questAgeRequired', 1);
		$this->setAttributeValue('questAgeVariable', 'age');

		$this->setAttributeValue('questCosActivate', 1);
		$this->setAttributeValue('questCosDescription', 'Course of Studies');
		$this->setAttributeValue('questCosCourses', "- Prefer not to say -\nEconomics\nManagement\n");
		$this->setAttributeValue('questCosMessage', 'Please select a course of studies');
		$this->setAttributeValue('questCosRequired', 1);
		$this->setAttributeValue('questCosVariable', 'cos');
	}

	private function initQuestions()
	{
		$questAdvanced = $this->getAttributeRuntimeValue('questAdvanced');

		//radio;variable name;required;default;errorMessage;options

		$this->_questions = @parse_ini_string($questAdvanced, true);
	}

	public function getQuestions()
	{
		if (is_null($this->_questions))
		{
			$this->initQuestions();
		}
		return $this->_questions;
	}

	public function renderForm()
	{
		$view = $this->getView();
		$translate = $this->getTranslate();
		$stepRender = $this->getStepRenderer();

		$content = '';
		// CONTENT

		$content .= '<form action="' . $this->getFrontUrl() . '" method="POST" name="stepaction">';
		$content .= $view->formHidden('contextChecksum', $this->getContext()->getChecksum());

		$content .= '<div id="caction">';
			$content .= '<div id="cactionform">';

				if (is_string($this->_processMessage) && $this->_processMessage != '')
				{
					$content .= '<div class="formError">' . $this->_processMessage . '</div>';
				}
				elseif (is_array($this->_processMessage) && sizeof($this->_processMessage) > 0)
				{
					$content .= '<div class="formError"><ul><li>' . implode('</li><li>', $this->_processMessage) . '</li></ul></div>';
				}

				// CODE
				//// pretext

			/*	$advancedQuestions = $this->getQuestions();

				if (sizeof($advancedQuestions) > 0)
				{
					$content .= '<div class="questAdvanced">';

						$questPreAdvanced = $stepRender->render($this->getAttributeRuntimeValue('questPreAdvanced'));
						if ($questPreAdvanced != '')
						{
							$content .= '<div class="questAdvancedPre">';
								$content .= $questPreAdvanced;
							$content .= '</div>';
						}

						foreach ($advancedQuestions as $question)
						{
							$content .= '<div class="questAdvancedQuestion">';
								$content .= $this->renderPart($question);
							$content .= '</div>';
						}
					$content .= '</div>';
				} */

				// STANDARD
				$genderActive = $this->getAttributeRuntimeValue('questGenderActivate');
				$ageActive = $this->getAttributeRuntimeValue('questAgeActivate');
				$cosActive = $this->getAttributeRuntimeValue('questCosActivate');

				if ($genderActive == '1' || $ageActive == '1' || $cosActive == '1')
				{

					$content .= '<div class="questStandard">';

					//// pretext
					$preTextStandard = $stepRender->render($this->getAttributeRuntimeValue('questPreStandard'));
					if ($preTextStandard != '')
					{
						$content .= '<div class="questStandardPre">';
							$content .= $preTextStandard;
						$content .= '</div>';
					}

					//// gender

					if ($genderActive)
					{
						$content .= '<div id="questStandardQuestionGender" class="questStandardQuestion">';

							$content .= '<div id="questStandardQuestionGenderHeadline" class="questStandardQuestionHeadline">';
								$content .= $this->getAttributeRuntimeValue('questGenderDescription');
							$content .= '</div>';

							$content .= '<div id="questStandardQuestionGenderForm" class="questStandardQuestionForm">';

								$female = $this->getAttributeRuntimeValue('questGenderFemale');
								$male = $this->getAttributeRuntimeValue('questGenderMale');
								$notSpecified = $this->getAttributeRuntimeValue('questGenderSpecified');

								$genderOptions = array ();
								if ($notSpecified != '')
								{
									$genderOptions['ns'] = $notSpecified;
								}
								if ($female != '')
								{
									$genderOptions['f'] = $female;
								}
								if ($male != '')
								{
									$genderOptions['m'] = $male;
								}


								$genderValue = $this->getContext()->getApi('variable')->getPSL($this->getAttributeRuntimeValue('questGenderVariable'));
								if (is_null($genderValue))
								{
									$content .= $view->formRadio('questGender', null, '', $genderOptions);
								}
								else
								{
									$content .= $view->formRadio('questGender', $genderValue, '', $genderOptions);
								}
							$content .= '</div>';
						$content .= '</div>';
					}

					//// age
					if ($ageActive)
					{
						$content .= '<div id="questStandardQuestionAge" class="questStandardQuestion">';

							$content .= '<div id="questStandardQuestionAgeHeadline" class="questStandardQuestionHeadline">';
								$content .= $this->getAttributeRuntimeValue('questAgeDescription');
							$content .= '</div>';

							$content .= '<div id="questStandardQuestionAgeForm" class="questStandardQuestionForm">';

								$ageValue = $this->getContext()->getApi('variable')->getPSL($this->getAttributeRuntimeValue('questAgeVariable'));
								if (is_null($ageValue))
								{
									$content .= $view->formText('questAge', null, array (
										'style' => 'width: 50px;'
									));
								}
								else
								{
									$content .= $view->formText('questAge', $ageValue, array (
										'style' => 'width: 50px;'
									));
								}

								$content .= ' ' . $this->getAttributeRuntimeValue('questAgeYears');

							$content .= '</div>';

						$content .= '</div>';
					}

					//// course of studies
					if ($cosActive)
					{
						$content .= '<div id="questStandardQuestionCos" class="questStandardQuestion">';

							$content .= '<div id="questStandardQuestionCosHeadline" class="questStandardQuestionHeadline">';
								$content .= $this->getAttributeRuntimeValue('questCosDescription');
							$content .= '</div>';

							$content .= '<div id="questStandardQuestionCosForm" class="questStandardQuestionForm">';

								$cos_courses = $this->getAttributeRuntimeValue('questCosCourses');
								$cosArray = $this->getAnswersArray($cos_courses);

								$cosValue = $this->getContext()->getApi('variable')->getPSL($this->getAttributeRuntimeValue('questCosVariable'));
								if (is_null($cosValue))
								{
									$content .= $view->formSelect('questCos', null, '', $cosArray);
								}
								else
								{
									$content .= $view->formSelect('questCos', $cosValue, '', $cosArray);
								}

							$content .= '</div>';

						$content .= '</div>';
					}

				$content .= '</div>';


			}

			$content .= '<input name="NextStep" type="submit" value="Weiter...">';

		$content .= '</div>';

		$content .= '</form>';

		return $content;
	}

	public function process()
	{

		$process = true;
		$variableApi = $this->getContext()->getApi('variable');

		$this->_processMessage = null;

		if ($this->getAttributeRuntimeValue('questAgeActivate'))
		{
			// age
			$ageRequired = $this->getAttributeRuntimeValue('questAgeRequired');
			$age = $this->getController()->getRequest()->getParam('questAge', '');
			if ($age == '')
			{
				if ($ageRequired)
				{
					$this->_processMessage .= $this->getAttributeRuntimeValue('questAgeMessage') . '<br />';
					$process = false;
				}
			}
			else
			{
				$variableApi->setPSL($this->getAttributeRuntimeValue('questAgeVariable'), $age);
			}
		}

		if ($this->getAttributeRuntimeValue('questCosActivate'))
		{
			// course of studies
			$cosRequired = $this->getAttributeRuntimeValue('questCosRequired');
			$cos = $this->getController()->getRequest()->getParam('questCos', '');
			if ($cos == '')
			{
				if ($cosRequired)
				{
					$this->_processMessage .= $this->getAttributeRuntimeValue('questCosMessage') . '<br />';
					$process = false;
				}
			}
			else
			{
				$variableApi->setPSL($this->getAttributeRuntimeValue('questCosVariable'), $cos);
			}
		}

		if ($this->getAttributeRuntimeValue('questGenderActivate'))
		{
			// gender
			$genderRequired = $this->getAttributeRuntimeValue('questGenderRequired');
			$gender = $this->getController()->getRequest()->getParam('questGender', '');
			if ($gender == '')
			{
				if ($genderRequired)
				{
					$this->_processMessage .= $this->getAttributeRuntimeValue('questGenderMessage') . '<br />';
					$process = false;
				}
			}
			else
			{
				$variableApi->setPSL($this->getAttributeRuntimeValue('questGenderVariable'), $gender);
			}
		}

	/*	// process advanced questions
		foreach ($this->getQuestions() as $question)
		{
			$value = $this->getController()->getRequest()->getParam($question['variable'], '');

			if ($value == '')
			{
				if ($question['required'])
				{
					$this->_processMessage .= $question['errorMessage'] . '<br />';
					$process = false;
				}
			}
			else
			{
				$variableApi->setPSL($this->getAttributeRuntimeValue($question['variable'], $value));
			}
		} */

		// process
		if ($process)
		{
			return parent::process();
		}
		return false;
	}

	public function getAnswersArray($text)
	{
		//return an array of all possible answers
		$answers = array ();
		$answerRows = explode("\n", $text);
		$i = 1;
		foreach ($answerRows as $answerRow)
		{
			$answerFields = explode(":", $answerRow, 2);
			if (sizeof($answerFields) == 1)
			{
				$answers[$i] = $answerFields[0];
				$i++;
			}
			else
			{
				$answers[$answerFields[0]] = $answerFields[1];
			}

		}
		return $answers;
	}

	public function getCodeArray($code)
	{
		$codeArray = array ();
		$types = array (
			'radio',
			'select'
		);
		$parts = explode(";", $code);

		$temp = array ();

		foreach ($parts as $part)
		{
			if (trim($part) == '')
			{
				break;
			}
			if (in_array(strtolower(trim($part)), $types))
			{

				if (!empty ($temp))
				{
					$codeArray[] = $temp;
					$temp = array ();
				}
				$temp[] = $part;
			}
			else
			{
				$temp[] = $part;
			}

		}
		$codeArray[] = $temp;
		return $codeArray;
	}

	public function renderPart($part)
	{
		$view = $this->getView();
		$params = $this->controller->getRequest()->getParams();

		$pattern = "/{[^}]*}/";
		preg_match_all($pattern, $part['options'], $matches);

		$matches = $matches[0];
		$variables = array ();

		// sets the value from the post
		$setValue = false;
		if (array_key_exists($part['variable'], $params))
		{
			$setValue = true;
		}

		foreach ($matches as $match)
		{
			trim($match);
			$variables[] = substr($match, 1, (strlen($match) - 2));
		}

		if ($part['type'] == 'radio')
		{

			$front = '<input type="radio" name="' . $part['variable'] . '" value="';

			foreach ($variables as $var)
			{
				if ($setValue)
				{
					// value was posted
					if ($params[$part['variable']] == $var)
					{
						$check = ' checked="checked"';
					}
					else
					{
						$check = '';
					}
				}
				else
				{
					//value was not posted
					if (trim($var) == $part['default'])
					{
						$check = ' checked="checked"';
					}
					else
					{
						$check = '';
					}

				}
				$end = '"' . $check . '>';
				$part['options'] = str_replace('{' . $var . '}', $front . $var . $end, $part['options']);
			}
		}

		elseif ($part['type'] == 'text')
		{
			if ($setValue)
			{
				echo 'set' . $params[$part['variable']];
				$input = '<input type="text" name="' . $part['variable'] . '" size="' . $part['size'] . '" maxlength="' . $part['maxlength'] . '" value="' . $params[$part['variable']] . '">';
			}
			else
			{
				$input = '<input type="text" name="' . $part['variable'] . '" size="' . $part['size'] . '" maxlength="' . $part['maxlength'] . '" value="' . $part['default'] . '">';
			}

			$part['options'] = str_replace('{' . $variables[0] . '}', $input, $part['options']);
		}

		elseif ($part['type'] == 'textarea')
		{
			if ($setValue)
			{
				$input = $view->formTextarea($part['variable'], $params[$part['variable']], array (
					'style' => 'width: ' . $part['width'] . 'px; height: ' . $part['height'] . 'px;'
				));

			}
			else
			{
				$input = $view->formTextarea($part['variable'], $part['default'], array (
					'style' => 'width: ' . $part['width'] . 'px; height: ' . $part['height'] . 'px;'
				));

			}

			$part['options'] = str_replace('{' . $variables[0] . '}', $input, $part['options']);
		}

		elseif ($part['type'] == 'checkbox')
		{
			foreach ($variables as $var)
			{
				$input = '<input type="checkbox" name="' . $part['variable'] . '" value="' . $var . '"  >';
				$part['options'] = str_replace('{' . $var . '}', $input, $part['options']);
			}
		}

		else
		{
			//die('undefined type: ' . print_r($part ,1));
            return '';
		}

		$content = '<br />' . $part['options'] . '<br />';
		return $content;
	}

	//////////////////////////////////////////

	public function adminGetTabs()
	{
		$tabs = parent::adminGetTabs();
		$tabs[] = array('id'=>'standard', 'title'=>'Standard', 'order'=>200);
	//	$tabs[] = array('id'=>'advanced', 'title'=>'Advanced', 'order'=>300);
		return $tabs;
	}

	// Start: STANDARD
	public function adminStandardTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subForm = $form->getSubForm('standard');
		if (is_null($subForm))
		{
			$subForm = $form->createSubForm();
	        $subForm->setAttribs(array(
	            'legend' => 'Standard',
	            'dijitParams' => array(
	                'title' => 'Standard',
	            ),
	        ));
			$form->addSubForm($subForm, 'standard');
		}

		$order = 100;
		$questPreStandard = $subForm->createElement('Textarea', 'questPreStandard', array('label'=>'Pre Standard', 'trim'=>'true', 'order'=>$order), array());
		$questPreStandard->setValue($this->getAttributeValue('questPreStandard'));
		$subForm->addElement($questPreStandard);

		///////////////////////////////////////////////////////////////////////////

		$order += 100;
		$questGenderBlankHeadline = $subForm->createElement('StaticHtml', 'questGenderBlankHeadline', array('label'=>'', 'order'=>$order), array());
		$questGenderBlankHeadline->setValue('');
		$subForm->addElement($questGenderBlankHeadline);

		$order += 100;
		$questGenderHeadline = $subForm->createElement('StaticHtml', 'questGenderHeadline', array('label'=>'Gender', 'order'=>$order), array());
		$questGenderHeadline->setValue('');
		$subForm->addElement($questGenderHeadline);

		$order += 100;
		$questGenderActivate = $subForm->createElement('Checkbox', 'questGenderActivate', array('label'=>'Activate', 'trim'=>'true', 'order'=>$order), array());
		$questGenderActivate->setValue($this->getAttributeValue('questGenderActivate'));
		$subForm->addElement($questGenderActivate);

		$order += 100;
		$questGenderDescription = $subForm->createElement('TextInput', 'questGenderDescription', array('label'=>'Field Label', 'trim'=>'true', 'order'=>$order), array());
		$questGenderDescription->setValue($this->getAttributeValue('questGenderDescription'));
		$subForm->addElement($questGenderDescription);

		$order += 100;
		$questGenderFemale = $subForm->createElement('TextInput', 'questGenderFemale', array('label'=>'Female Label', 'trim'=>'true', 'order'=>$order), array());
		$questGenderFemale->setValue($this->getAttributeValue('questGenderFemale'));
		$subForm->addElement($questGenderFemale);

		$order += 100;
		$questGenderMale = $subForm->createElement('TextInput', 'questGenderMale', array('label'=>'Male Label', 'trim'=>'true', 'order'=>$order), array());
		$questGenderMale->setValue($this->getAttributeValue('questGenderMale'));
		$subForm->addElement($questGenderMale);

		$order += 100;
		$questGenderSpecified = $subForm->createElement('TextInput', 'questGenderSpecified', array('label'=>'Unspecified Label', 'trim'=>'true', 'order'=>$order), array());
		$questGenderSpecified->setValue($this->getAttributeValue('questGenderSpecified'));
		$subForm->addElement($questGenderSpecified);

		$order += 100;
		$questGenderVariable = $subForm->createElement('TextInput', 'questGenderVariable', array('label'=>'Variable', 'trim'=>'true', 'order'=>$order), array());
		$questGenderVariable->setValue($this->getAttributeValue('questGenderVariable'));
		$subForm->addElement($questGenderVariable);

		$order += 100;
		$questGenderRequired = $subForm->createElement('Checkbox', 'questGenderRequired', array('label'=>'Required', 'trim'=>'true', 'order'=>$order), array());
		$questGenderRequired->setValue($this->getAttributeValue('questGenderRequired'));
		$subForm->addElement($questGenderRequired);

		$order += 100;
		$questGenderMessage = $subForm->createElement('TextInput', 'questGenderMessage', array('label'=>'Error Message', 'trim'=>'true', 'order'=>$order), array());
		$questGenderMessage->setValue($this->getAttributeValue('questGenderMessage'));
		$subForm->addElement($questGenderMessage);

		///////////////////////////////////////////////////////////////////////////

		$order += 100;
		$questAgeBlankHeadline = $subForm->createElement('StaticHtml', 'questAgeBlankHeadline', array('label'=>'', 'order'=>$order), array());
		$questAgeBlankHeadline->setValue('');
		$subForm->addElement($questAgeBlankHeadline);

		$order += 100;
		$questAgeHeadline = $subForm->createElement('StaticHtml', 'questAgeHeadline', array('label'=>'Age', 'order'=>$order), array());
		$questAgeHeadline->setValue('');
		$subForm->addElement($questAgeHeadline);

		$order += 100;
		$questAgeActivate = $subForm->createElement('Checkbox', 'questAgeActivate', array('label'=>'Activate', 'trim'=>'true', 'order'=>$order), array());
		$questAgeActivate->setValue($this->getAttributeValue('questAgeActivate'));
		$subForm->addElement($questAgeActivate);

		$order += 100;
		$questAgeDescription = $subForm->createElement('TextInput', 'questAgeDescription', array('label'=>'Field Label', 'trim'=>'true', 'order'=>$order), array());
		$questAgeDescription->setValue($this->getAttributeValue('questAgeDescription'));
		$subForm->addElement($questAgeDescription);

		$order += 100;
		$questAgeYears = $subForm->createElement('TextInput', 'questAgeYears', array('label'=>'Years', 'trim'=>'true', 'order'=>$order), array());
		$questAgeYears->setValue($this->getAttributeValue('questAgeYears'));
		$subForm->addElement($questAgeYears);

		$order += 100;
		$questAgeVariable = $subForm->createElement('TextInput', 'questAgeVariable', array('label'=>'Age Variable', 'trim'=>'true', 'order'=>$order), array());
		$questAgeVariable->setValue($this->getAttributeValue('questAgeVariable'));
		$subForm->addElement($questAgeVariable);

		$order += 100;
		$questAgeRequired = $subForm->createElement('Checkbox', 'questAgeRequired', array('label'=>'Required', 'trim'=>'true', 'order'=>$order), array());
		$questAgeRequired->setValue($this->getAttributeValue('questAgeRequired'));
		$subForm->addElement($questAgeRequired);

		$order += 100;
		$questAgeMessage = $subForm->createElement('TextInput', 'questAgeMessage', array('label'=>'Error Message', 'trim'=>'true', 'order'=>$order), array());
		$questAgeMessage->setValue($this->getAttributeValue('questAgeMessage'));
		$subForm->addElement($questAgeMessage);

		///////////////////////////////////////////////////////////////////////////

		$order += 100;
		$questCosBlankHeadline = $subForm->createElement('StaticHtml', 'questCosBlankHeadline', array('label'=>'', 'order'=>$order), array());
		$questCosBlankHeadline->setValue('');
		$subForm->addElement($questCosBlankHeadline);

		$order += 100;
		$questCosHeadline = $subForm->createElement('StaticHtml', 'questCosHeadline', array('label'=>'Course of Studies', 'order'=>$order), array());
		$questCosHeadline->setValue('');
		$subForm->addElement($questCosHeadline);

		$order += 100;
		$questCosActivate = $subForm->createElement('Checkbox', 'questCosActivate', array('label'=>'Activate', 'trim'=>'true', 'order'=>$order), array());
		$questCosActivate->setValue($this->getAttributeValue('questCosActivate'));
		$subForm->addElement($questCosActivate);

		$order += 100;
		$questCosDescription = $subForm->createElement('TextInput', 'questCosDescription', array('label'=>'Field Label', 'trim'=>'true', 'order'=>$order), array());
		$questCosDescription->setValue($this->getAttributeValue('questCosDescription'));
		$subForm->addElement($questCosDescription);

		$order += 100;
		$questCosCourses = $subForm->createElement('Textarea', 'questCosCourses', array('label'=>'Courses', 'trim'=>'true', 'order'=>$order), array());
		$questCosCourses->setValue($this->getAttributeValue('questCosCourses'));
		$subForm->addElement($questCosCourses);

		$order += 100;
		$questCosVariable = $subForm->createElement('TextInput', 'questCosVariable', array('label'=>'Variable', 'trim'=>'true', 'order'=>$order), array());
		$questCosVariable->setValue($this->getAttributeValue('questCosVariable'));
		$subForm->addElement($questCosVariable);

		$order += 100;
		$questCosRequired = $subForm->createElement('Checkbox', 'questCosRequired', array('label'=>'Required', 'trim'=>'true', 'order'=>$order), array());
		$questCosRequired->setValue($this->getAttributeValue('questCosRequired'));
		$subForm->addElement($questCosRequired);

		$order += 100;
		$questCosMessage = $subForm->createElement('TextInput', 'questCosMessage', array('label'=>'Error Message', 'trim'=>'true', 'order'=>$order), array());
		$questCosMessage->setValue($this->getAttributeValue('questCosMessage'));
		$subForm->addElement($questCosMessage);

		///////////////////////////////////////////////////////////////////////////

		$order += 100;
		$submit = $subForm->createElement('submit', 'questStdSave', array('label'=>'Save', 'order'=>$order, 'ignore'=>'true'));
		$subForm->addElement($submit);
	}

	public function adminStandardTabProcess($parameters)
	{
		$this->setAttributeValue('questPreStandard', $parameters['questPreStandard']);

		$this->setAttributeValue('questGenderActivate', $parameters['questGenderActivate']);
		$this->setAttributeValue('questGenderDescription', $parameters['questGenderDescription']);
		$this->setAttributeValue('questGenderFemale', $parameters['questGenderFemale']);
		$this->setAttributeValue('questGenderMale', $parameters['questGenderMale']);
		$this->setAttributeValue('questGenderSpecified', $parameters['questGenderSpecified']);
		$this->setAttributeValue('questGenderVariable', $parameters['questGenderVariable']);
		$this->setAttributeValue('questGenderRequired', $parameters['questGenderRequired']);
		$this->setAttributeValue('questGenderMessage', $parameters['questGenderMessage']);

		$this->setAttributeValue('questAgeActivate', $parameters['questAgeActivate']);
		$this->setAttributeValue('questAgeYears', $parameters['questAgeYears']);
		$this->setAttributeValue('questAgeDescription', $parameters['questAgeDescription']);
		$this->setAttributeValue('questAgeVariable', $parameters['questAgeVariable']);
		$this->setAttributeValue('questAgeRequired', $parameters['questAgeRequired']);
		$this->setAttributeValue('questAgeMessage', $parameters['questAgeMessage']);

		$this->setAttributeValue('questCosActivate', $parameters['questCosActivate']);
		$this->setAttributeValue('questCosDescription', $parameters['questCosDescription']);
		$this->setAttributeValue('questCosCourses', $parameters['questCosCourses']);
		$this->setAttributeValue('questCosVariable', $parameters['questCosVariable']);
		$this->setAttributeValue('questCosRequired', $parameters['questCosRequired']);
		$this->setAttributeValue('questCosMessage', $parameters['questCosMessage']);
	}

	public function adminAdvancedTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subForm = $form->getSubForm('advanced');
		if (is_null($subForm))
		{
			$subForm = $form->createSubForm();
	        $subForm->setAttribs(array(
	            'legend' => 'Advanced',
	            'dijitParams' => array(
	                'title' => 'Advanced',
	            ),
	        ));
			$form->addSubForm($subForm, 'advanced');
		}

		$order = 100;
		$questPreAdvanced = $subForm->createElement('Textarea', 'questPreAdvanced', array('label'=>'Pre Advanced', 'trim'=>'true', 'order'=>$order), array());
		$questPreAdvanced->setValue($this->getAttributeValue('questPreAdvanced'));
		$subForm->addElement($questPreAdvanced);

		$order += 100;
		$questAdvanced = $subForm->createElement('SimpleTextarea', 'Textarea', array('label'=>'Advanced', 'trim'=>'true', 'order'=>$order), array());
		$questAdvanced->setValue($this->getAttributeValue('questAdvanced'));
		$subForm->addElement($questAdvanced);

		$order += 100;
		$submit = $subForm->createElement('submit', 'questAdvSave', array('label'=>'Save', 'order'=>$order, 'ignore'=>'true'));
		$subForm->addElement($submit);
	}

	public function adminAdvancedTabProcess($parameters)
	{
		$this->setAttributeValue('questPreAdvanced', $parameters['questPreAdvanced']);
		$this->setAttributeValue('questAdvanced', $parameters['questAdvanced']);
	}

}