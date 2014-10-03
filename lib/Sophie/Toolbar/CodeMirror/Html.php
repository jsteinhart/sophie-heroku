<?php
class Sophie_Toolbar_CodeMirror_Html extends Sophie_Toolbar_CodeMirror
{
	public function render($view, $codeMirrorJsObjectName, $options = null)
	{
		if (!is_null($options))
		{
			$this->options = $options;
		}
		else
		{
			$treatmentId = (is_null($this->steptype)) ? null : $this->steptype->getContext()->getTreatmentId();
			$this->options = array (
				array (
					'title' => 'context',
					'htmlTitle' => '<tt>$context</tt>',
					'items' => array (
						/*array(
							'title' => 'General',
							'items' => array(
								array(
									'htmlTitle' => '<tt>getChecksum</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getChecksum')"
									)
								),
							),

						array(
							'title' => 'Experiment',
							'items' => array(
								array(
									'htmlTitle' => '<tt>getExperimentId</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getExperimentId')"
									),
								array(
									'htmlTitle' => '<tt>getExperiment</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getExperiment')"
									)
								),
							),

						array(
							'title' => 'Treatment',
							'items' => array(
								array(
									'htmlTitle' => '<tt>getTreatmentId</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getTreatmentId')"
									),
								array(
									'htmlTitle' => '<tt>getTreatment</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getTreatment')"
									)
								),
							),
						*/
						array (
							'title' => 'Stepgroup',
							'items' => array (
								array (
									'htmlTitle' => '<tt>getStepgroupLabel</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getStepgroupLabel')"
								),
								/*	array(
										'htmlTitle' => '<tt>getStepgroupId</tt>',
										'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getStepgroupId')"
										),
									array(
										'htmlTitle' => '<tt>getStepgroup</tt>',
										'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getStepgroup')"
										),
								*/
								array (
									'htmlTitle' => '<tt>getStepgroupLoop</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getStepgroupLoop')"
								)
							),
						),
						/*
											array(
												'title' => 'Step',
												'items' => array(
													array(
														'htmlTitle' => '<tt>getStepId</tt>',
														'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getStepId')"
														),
													array(
														'htmlTitle' => '<tt>getStep</tt>',
														'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getStep')"
														)
													),
												),

											array(
												'title' => 'Session',
												'items' => array(
													array(
														'htmlTitle' => '<tt>getSessionId</tt>',
														'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getSessionId')"
														),
													array(
														'htmlTitle' => '<tt>getSession</tt>',
														'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getSession')"
														)
													),
												),
						*/
						array (
							'title' => 'Group',
							'items' => array (
								array (
									'htmlTitle' => '<tt>getGroupLabel</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getGroupLabel')"
								)
							),
						),

						array (
							'title' => 'Participant',
							'items' => array (
								array (
									'htmlTitle' => '<tt>getParticipantLabel</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getParticipantLabel')"
								),
								/*	array(
										'htmlTitle' => '<tt>getParticipant</tt>',
										'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getParticipant')"
										),
										*/
								array (
									'htmlTitle' => '<tt>getParticipantTypeLabel</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getParticipantTypeLabel')"
								)
							),
						),
					)
				),
				array (
					'title' => 'variableApi',
					'htmlTitle' => '<tt>$variableApi</tt>',
					'items' => array (
						array (
							'title' => 'Everyone / Everywhere',
							'items' => array (
								array (
									'htmlTitle' => '<tt>getEE</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getEE')"
								),
								array (
									'htmlTitle' => '<tt>setEE</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'setEE')"
								),
								array (
									'type' => 'separator',


								),
								array (
									'htmlTitle' => '<tt>getEE</tt> (with all parameters)',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getEEFull')"
								),
								array (
									'htmlTitle' => '<tt>setEE</tt> (with all parameters)',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'setEEFull')"
								),
							),
						),

						array (
							'title' => 'Everyone / Stepgroup',
							'items' => array (
								array (
									'htmlTitle' => '<tt>getES</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getES')"
								),
								array (
									'htmlTitle' => '<tt>setES</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'setES')"
								),
								array (
									'type' => 'separator',
								),
								array (
									'htmlTitle' => '<tt>getES</tt> (with all parameters)',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getESFull')"
								),
								array (
									'htmlTitle' => '<tt>setES</tt> (with all parameters)',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'setESFull')"
								),
							),
						),
						array (
							'title' => 'Everyone / Stepgroup Loop',
							'items' => array (
								array (
									'htmlTitle' => '<tt>getESL</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getESL')"
								),
								array (
									'htmlTitle' => '<tt>setESL</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'setESL')"
								),
								array (
									'type' => 'separator',
								),
								array (
									'htmlTitle' => '<tt>getESL</tt> (with all parameters)',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getESLFull')"
								),
								array (
									'htmlTitle' => '<tt>setESL</tt> (with all parameters)',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'setESLFull')"
								),
							),
						),
						array (
							'title' => 'Group / Everywhere',
							'items' => array (
								array (
									'htmlTitle' => '<tt>getGE</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getGE')"
								),
								array (
									'htmlTitle' => '<tt>setGE</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'setGE')"
								),
								array (
									'type' => 'separator',
								),
								array (
									'htmlTitle' => '<tt>getGE</tt> (with all parameters)',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getGEFull')"
								),
								array (
									'htmlTitle' => '<tt>setGE</tt> (with all parameters)',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'setGEFull')"
								),
							),
						),
						array (
							'title' => 'Group / Stepgroup',
							'items' => array (
								array (
									'htmlTitle' => '<tt>getGS</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getGS')"
								),
								array (
									'htmlTitle' => '<tt>setGS</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'setGS')"
								),
								array (
									'type' => 'separator',
								),
								array (
									'htmlTitle' => '<tt>getGS</tt> (with all parameters)',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getGSFull')"
								),
								array (
									'htmlTitle' => '<tt>setGS</tt> (with all parameters)',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'setGSFull')"
								),
							),
						),
						array (
							'title' => 'Group / Stepgroup Loop',
							'items' => array (
								array (
									'htmlTitle' => '<tt>getGSL</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getGSL')"
								),
								array (
									'htmlTitle' => '<tt>setGSL</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'setGSL')"
								),
								array (
									'type' => 'separator',
								),
								array (
									'htmlTitle' => '<tt>getGSL</tt> (with all parameters)',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getGSLFull')"
								),
								array (
									'htmlTitle' => '<tt>setGSL</tt> (with all parameters)',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'setGSLFull')"
								),
							),
						),
						array (
							'title' => 'Participant / Everywhere',
							'items' => array (
								array (
									'htmlTitle' => '<tt>getPE</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getPE')"
								),
								array (
									'htmlTitle' => '<tt>setPE</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'setPE')"
								),
								array (
									'type' => 'separator',
								),
								array (
									'htmlTitle' => '<tt>getPE</tt> (with all parameters)',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getPEFull')"
								),
								array (
									'htmlTitle' => '<tt>setPE</tt> (with all parameters)',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'setPEFull')"
								),
							),
						),
						array (
							'title' => 'Participant / Stepgroup',
							'items' => array (
								array (
									'htmlTitle' => '<tt>getPS</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getPS')"
								),
								array (
									'htmlTitle' => '<tt>setPS</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'setPS')"
								),
								array (
									'type' => 'separator',
								),
								array (
									'htmlTitle' => '<tt>getPS</tt> (with all parameters)',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getPSFull')"
								),
								array (
									'htmlTitle' => '<tt>setPS</tt> (with all parameters)',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'setPSFull')"
								),
							),
						),
						array (
							'title' => 'Participant / Stepgroup Loop',
							'items' => array (
								array (
									'htmlTitle' => '<tt>getPSL</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getPSL')"
								),
								array (
									'htmlTitle' => '<tt>setPSL</tt>',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'setPSL')"
								),
								array (
									'type' => 'separator',
								),
								array (
									'htmlTitle' => '<tt>getPSL</tt> (with all parameters)',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'getPSLFull')"
								),
								array (
									'htmlTitle' => '<tt>setPSL</tt> (with all parameters)',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'setPSLFull')"
								),
							),
						),
					)
				),

				array (
					'title' => 'assetApi',
					'htmlTitle' => '<tt>$assetApi</tt>',

					'items' => array (
						array (
							'htmlTitle' => '<tt>inlineImg</tt> ',
							'onclick' => "sophieGetAssets('" . $treatmentId . "', '" . $codeMirrorJsObjectName . "')"
						),
						array (
							'htmlTitle' => '<tt>inlineData</tt> ',
							'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'assetInlineData')"
						)
					)
				),

				array (
					'htmlTitle' => '|'
				),

				array (
					'title' => 'HTML',
					'htmlTitle' => '<tt>HTML</tt>',
					'items' => array (
						array (
							'title' => 'Table',
							'items' => array (
								array (
									'htmlTitle' => '2 x 2',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'table2x2')"
								),
								array (
									'htmlTitle' => '3 x 3',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'table3x3')"
								),
								array (
									'htmlTitle' => '4 x 4',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'table4x4')"
								)
							),
						),

						array (
							'title' => 'List',
							'items' => array (
								array (
									'htmlTitle' => 'Unordered List',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'ul')"
								),
								array (
									'htmlTitle' => 'Unordered List: squares',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'ul-squares')"
								),
								array (
									'type' => 'separator',
								),
								array (
									'htmlTitle' => 'Ordered List',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'ol')"
								),
								array (
									'htmlTitle' => 'Ordered List: roman',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'ol-roman')"
								)
							),
						),
					)
				),

				array (
					'title' => 'PHP',
					'htmlTitle' => '<tt>PHP</tt>',
					'items' => array (
						array (
							'title' => 'If',
							'items' => array (
								array (
									'htmlTitle' => 'If...',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'if')"
								),
								array (
									'htmlTitle' => 'If...else...',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'if-else')"
								),
								array (
									'htmlTitle' => 'If...elseif....else',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'if-elseif-else')"
								)
							),
						),
						array (
							'title' => 'For',
							'items' => array (
								array (
									'htmlTitle' => 'For...',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'for')"
								),
								array (
									'htmlTitle' => 'Foreach...',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'foreach')"
								)
							),
						),
						array (
							'title' => 'Switch',
							'items' => array (
								array (
									'htmlTitle' => 'Switch...case',
									'onclick' => "expdesigner.insertCodeExample(" . $codeMirrorJsObjectName . ", 'switch')"
								)
							)
						)
					)
				),

				array (
					'htmlTitle' => '|',
				),

				array (
					'title' => 'Undo',
					'htmlTitle' => '<img src="/_media/Icons/arrow_undo.png" alt="Undo"  />',
					'onclick' => $codeMirrorJsObjectName . '.undo()'
				),
				array (
					'title' => 'Redo',
					'htmlTitle' => '<img src="/_media/Icons/arrow_redo.png" alt="Redo" />',
					'onclick' => $codeMirrorJsObjectName . '.redo()'
				),
				array (
					'title' => 'Toggle Line Wrap',
					'htmlTitle' => '<img src="/_media/Icons/text_align_justify.png" alt="Toggle Line Wrap" />',
					'onclick' => $codeMirrorJsObjectName . '.toggleLineWrapping()'
				),
				array (
					'title' => 'Toggle Fullscreen',
					'htmlTitle' => '<img src="/_media/Icons/shape_square.png" alt="Toggle Fullscreen" />',
					'onclick' => $codeMirrorJsObjectName . '.toggleFullscreen(); alert(\'Return from Fullscreen with ESC or F11\');'
				),
			);
		}

		return parent :: render($view, $codeMirrorJsObjectName, $this->options);
	}
}