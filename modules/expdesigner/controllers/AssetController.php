<?php
class Expdesigner_AssetController extends Symbic_Controller_Action
{
	private $experimentId = null;
	private $treatmentId = null;
	private $assetId = null;

	private $experiment = null;
	private $treatment = null;
	private $asset = null;

	public function preDispatch()
	{
		$this->assetId = $this->_getParam('assetId', null);
		if ($this->assetId)
		{
			$this->asset = Sophie_Db_Treatment_Asset :: getInstance()->find($this->assetId)->current();
			if (is_null($this->asset))
			{
				$this->_error('Selected asset does not exist!');
				return;
			}
			$this->treatmentId = $this->asset->treatmentId;
		}
		else
		{
			$this->treatmentId = $this->_getParam('treatmentId', null);
		}

		if (empty ($this->assetId) && empty ($this->treatmentId))
		{
			$this->_error('Paramater assetId or treatmentId missing!');
			return;
		}

		$this->treatment = Sophie_Db_Treatment :: getInstance()->find($this->treatmentId)->current();
		if (is_null($this->treatment))
		{
			$this->_error('Selected treatment does not exist!');
			return;
		}
		$this->experiment = $this->treatment->findParentRow('Sophie_Db_Experiment');
		$this->experimentId = $this->experiment->id;

		$acl = System_Acl :: getInstance();
		if (!$acl->autoCheckAcl('experiment', $this->experiment->id, 'sophie_experiment'))
		{
			$this->_error('Access denied.');
			return;
		}

		$this->view->breadcrumbs = array (
			'home' => 'expdesigner',
			'experiment' => array (
				'id' => $this->experiment->id,
				'name' => $this->experiment->name
			),
			'treatment' => array (
				'id' => $this->treatment->id,
				'name' => $this->treatment->name,
				'anchor' => 'tab_treatmentAssetTab'
			)
		);

	}

	public function indexAction()
	{
		//Show existing assets
		$assetModel = Sophie_Db_Treatment_Asset :: getInstance();
		$assets = $assetModel->getAssetsByTreatmentId($this->treatmentId);
		$this->view->assets = $assets;

		$this->view->treatmentId = $this->treatmentId;
		$this->_helper->layout->disableLayout();
	}

	public function showAction()
	{
		if (is_null($this->asset))
		{
			$this->_error('Selected asset does not exist!');
			return;
		}

		header('Content-type: ' . $this->asset->contentType);
		if ($this->asset->contentType == 'application/octet-stream')
		{
			header('Content-Disposition: attachment; filename="' . $this->asset->label . '"');
		}
		else
		{
			header('Content-Disposition: inline; filename="' . $this->asset->label . '"');
		}
		echo $this->asset->data;
		exit;

		$this->_helper->layout->disableLayout();
	}

	public function downloadAction()
	{
		if (is_null($this->asset))
		{
			$this->_error('Selected asset does not exist!');
			return;
		}

		header('Content-type: ' . $this->asset->contentType);
		header('Content-Disposition: attachment; filename="' . $this->asset->label . '"');
		echo $this->asset->data;
		exit;

		$this->_helper->layout->disableLayout();
	}
	
	public function addAction()
	{
		$post = $this->getRequest();
		$form = $this->getForm('Asset_Add');
		$form->populate(array('treatmentId' => $this->treatmentId));
		$form->setAttrib('enctype', 'multipart/form-data');

		$labelElement = $form->getElement('label');
		$labelValidator = new Sophie_Validate_Treatment_Asset_Label();
		$labelValidator->treatmentId = $this->treatment->id;
		$labelValidator->setUniqueCheck(true);
		$labelElement->addValidator($labelValidator, true);

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				if (!$form->data->receive())
				{
					$this->_error('An error occurred while receiving the file.');
				}
				else
				{
					$data = $form->getValues();

					$assetFilePath = $form->data->getFileName();
					$assetFileName = $form->data->getFileName(null, false);

					if ($data['label'] == '')
					{
						$data['label'] = $assetFileName;
					}

					$assetMetadata = array();
					$assetMetadata['mime'] = 'application/octet-stream';
					$assetInfo = array();
					
					$lastExt = strrpos($assetFileName, '.');
					if ($lastExt !== FALSE)
					{
						$ext = strtolower(substr($assetFileName, $lastExt + 1));

						switch ($ext)
						{
							case 'png':
							case 'gif':
							case 'jpeg':
							case 'jpg':
								if (exif_imagetype($assetFilePath) !== FALSE)
								{
									try
									{
										$assetMetadata = @getimagesize($assetFilePath, $assetInfo);
									}
									catch (Exception $e)
									{
									}

									if ($assetMetadata === false || !is_array($assetMetadata))
									{
										$assetMetadata = array();
									}

									if ($assetInfo === false || !is_array($assetInfo))
									{
										$assetMetadata['assetInfo'] = array();
									}
									else
									{
										$assetMetadata['assetInfo'] = $assetInfo;
									}

								}
								
								if (empty($assetMetadata['mime']))
								{
									if ($ext == 'png')
									{
										$assetMetadata['mime'] = 'image/png';
									}
									elseif ($ext == 'gif')
									{
										$assetMetadata['mime'] = 'image/gif';
									}
									elseif ($ext == 'jpeg' || $ext == 'jpg')
									{
										$assetMetadata['mime'] = 'image/jpeg';
									}
								}
								break;
								
							case 'mp3':
								$assetMetadata['mime'] = 'audio/mpeg';
								break;

							case 'wav':
								$assetMetadata['mime'] = 'audio/wav';
								break;

							case 'ogg':
								// TODO: differentiate from video/ogg
								$assetMetadata['mime'] = 'audio/ogg';
								break;

							case 'mp4':
								$assetMetadata['mime'] = 'video/mp4';
								break;

							case 'webm':
								$assetMetadata['mime'] = 'video/webm';
								break;

							case 'sfw':
								$assetMetadata['mime'] = 'application/shockwave';
								break;

							case 'pdf':
								$assetMetadata['mime'] = 'application/pdf';
								break;
								
							default:
								$assetMetadata['contentType'] = 'application/octet-stream';
								// TODO: maybe reject these kind of files?
						}
					}

					if (isset($assetMetadata['mime']) && $assetMetadata['mime'] != '')
					{
						$data['contentType'] = $assetMetadata['mime'];
					}
					else
					{
						$data['contentType'] = 'application/octet-stream';
					}

					$data['metadata'] = Zend_Json::encode($assetMetadata);
					$data['data'] = file_get_contents($assetFilePath);

					$assetModel = Sophie_Db_Treatment_Asset :: getInstance();
					$assetModel->replace($data);

					$this->_helper->getHelper('Redirector')->setPrependBase('')->gotoUrl($this->_helper->getHelper('Url')->url(array (
						'module' => 'expdesigner',
						'controller' => 'treatment',
						'action' => 'details',
						'treatmentId' => $data['treatmentId']
					)) . '#tab_treatmentAssetTab');
				}
			}
		}

		$this->view->breadcrumbs[] = array (
			'title' => 'Add Asset',
			'small' => 'Asset:',
			'name' => 'Add Asset'
		);

		$this->view->form = $form;
	}

	public function editAction()
	{
		if (is_null($this->asset))
		{
			$this->_error('Selected asset does not exist!');
			return;
		}

		$post = $this->getRequest();
		$form = $this->getForm('Asset_Edit');
		$form->populate(array('treatmentId' => $this->treatmentId));
		$form->setAttrib('enctype', 'multipart/form-data');

		$labelElement = $form->getElement('label');
		$labelValidator = new Sophie_Validate_Treatment_Asset_Label();
		$labelValidator->treatmentId = $this->treatment->id;
		$labelValidator->assetId = $this->asset->id;
		$labelValidator->setUniqueCheck(true);
		$labelElement->addValidator($labelValidator, true);

		$form->setDefaults($this->asset->toArray());

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$data = $form->getValues();

				$this->asset->setFromArray($data);
				$this->asset->save();

				$this->_helper->getHelper('Redirector')->setPrependBase('')->gotoUrl($this->_helper->getHelper('Url')->url(array (
					'module' => 'expdesigner',
					'controller' => 'treatment',
					'action' => 'details',
					'treatmentId' => $data['treatmentId']
				)) . '#tab_treatmentAssetTab');
				return;
			}
		}

		$this->view->breadcrumbs[] = array (
			'title' => 'Edit Asset',
			'small' => 'Asset:',
			'name' => 'Edit Asset'
		);

		$this->view->form = $form;
	}
	
	public function getassetsAction()
	{
		$codeMirrorId = $this->_getParam('codeMirrorId');

		$assetModel = Sophie_Db_Treatment_Asset :: getInstance();
		$result = $assetModel->getAssetsByTreatmentId($this->_getParam('treatmentId'));

		if (count($result) == 0)
		{
			$content = 'No assets found.';
		}
		else
		{

			$content = "<table><tr><th>Label</th><th>Preview</th></tr>";

			foreach ($result as $asset)
			{
				$content .= '<tr>';
				$content .= '<td><a class="mousePointer" onclick="sophieInsertCodeExample(' . $codeMirrorId . ", 'assetInlineImg', '" . $asset->label . "'); dijit.byId('assetChooser').destroy();\">" . $asset->label . "</a></td>";
				$content .= '<td>';

				if (stripos($asset->contentType, 'image/') === 0)
				{
					$metadata = Zend_Json::decode($asset->metadata);

					if (!isset($metadata[0]) || !isset($metadata[1]) || !isset($metadata[2]))
					{
						echo 'No Preview available';
					}
					else
					{
						if($metadata[0] > 100 || $metadata[1] > 100)
						{
							$format = $metadata[0] > $metadata[1]? 'width = "100px"': ' height = "100px"';
						}
						else
						{
							$format = $metadata[2];
						}

						$showAssetUrl = $this->_helper->getHelper('Url')->url(
								array(
									'module' => 'expdesigner',
									'controller' => 'asset',
									'action' => 'show',
									'assetId' => $asset->id
								)
							);
						$content .= '<a class="mousePointer" onclick="sophieInsertCodeExample(' . $codeMirrorId . ", 'assetInlineImg', '" . $asset->label . "'); dijit.byId('assetChooser').destroy();\">" . '<img src="' . $showAssetUrl . '"' . $format . ' /></a>';

					}
				}
				else
				{
					$content .= 'No Preview available';
				}
				$content .= "</td></tr>";

			}
			$content .= "</table>";
		}

		$answer = array ();
		$answer['content'] = $content;

		$this->_helper->json($answer);
	}

	public function deleteAction()
	{
		if (is_null($this->asset))
		{
			$this->_error('Selected asset does not exist!');
			return;
		}

		$this->asset->delete();

		$this->_helper->json(array (
			'message' => 'Asset deleted'
		));
	}
}