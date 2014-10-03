(function( window, $, dojo, dijit, bootbox, console, rateLimitCall, undefined)
{
	var module		= {},
		baseUrl		= '/';

	module.name								= 'expdesigner';
	module.baseUrl							= baseUrl + module.name;

	module.experimentId						= null;
	module.treatmentId						= null;
	module.sessionDetailsLastLogId			= null;
	module.sessionDetailsTimerSessionId		= null;
	module.sessionDetailsTimerRunning		= false;

	module.returnToPosition					= null;

	module.routes = {
		experiment:						module.baseUrl + '/experiment/index',
		deleteExperiment:				module.baseUrl + '/experiment/delete',
		treatment:						module.baseUrl + '/treatment/index',
		deleteTreatment:				module.baseUrl + '/treatment/delete',
		treatmentDetails:				module.baseUrl + '/treatment/details',
		treatmentCheckpayoffscriptcode:	module.baseUrl + '/treatment/checkpayoffscriptcode',
		treatmentChecksetupscriptcode:	module.baseUrl + '/treatment/checksetupscriptcode',

		treatmentVariableEdit:			module.baseUrl + '/variable/edit',
		treatmentVariableDelete:		module.baseUrl + '/variable/delete',
		treatmentVariableDeleteall:		module.baseUrl + '/variable/deleteall',

		stepAdd:						module.baseUrl + '/step/add',
		stepCheckcode:					module.baseUrl + '/step/checkcode',

		reportCheckcode:				module.baseUrl + '/report/checkcode',
	};

	module.evalXhrResult = function (result)
	{
		if (result.hasOwnProperty('error'))
		{
			dojo.publish('messages', [{ message: result.error, type: 'error'}]);
			return false;
		}

		if (result.hasOwnProperty('message'))
		{
			if (result.hasOwnProperty('type'))
			{
				dojo.publish('messages', [{ message: result.message, type: result.type}]);
				if (result.type === 'error')
				{
					return false;
				}
				return true;
			}

			dojo.publish('messages', [{ message: result.message, type: "message"}]);
			return true;
		}

		dojo.publish('messages', [{ message: 'Request succeeded', type: "message"}]);
		return true;
	};

	module.initExperimentList = function()
	{
		$('#experimentList').dataTable(
		{
			"aoColumnDefs":
			[
			  { "bSearchable": false, "aTargets": [ 3 ] },
			  { "bSortable": false, "aTargets": [ 3 ] }
			]
		});
		$('#experimentList tbody').on(
			"dblclick",
			"tr",
			function(e)
			{
				window.location.href = module.routes.treatment + "/experimentId/" + e.currentTarget.getAttribute('data-pk');
				e.stopPropagation();
			}
		);
		$('#experimentList tbody').on(
			"click",
			"button.expdesignerSelectExperiment",
			function(e)
			{
				var id = $(e.currentTarget).closest('tr').attr('data-pk');
				window.location.href = module.routes.treatment + "/experimentId/" + id;
				e.stopPropagation();
			}
		);
		$('#experimentList tbody').on(
			"click",
			"button.expdesignerDeleteExperiment",
			function(e)
			{
				var id = $(e.currentTarget).closest('tr').attr('data-pk');
				module.deleteExperiment(id);
				e.stopPropagation();
			}
		);
	};

	module.deleteExperiment = function(experimentId)
	{
		bootbox.confirm(
			'Do you really want to delete this experiment?',
			function(result)
			{
				if (result)
				{
					$.ajax(
						{
							url: module.routes.deleteExperiment,
							type: 'POST',
							data: {
								experimentId:	experimentId
							},
							cache: false,
							success: function(data, textStatus, jqXHR)
							{
								var row;

								if (module.evalXhrResult(result))
								{
									row = $('#experimentList tbody tr[data-pk="' + experimentId + '"]');
									if (row)
									{
										row.remove();
									}
									else
									{
										window.location.href = module.routes.experimentList;
									}
								}
							},
							error: function()
							{
								dojo.publish('messages', [{ message: 'Deleting experiment failed', type: 'error'}]);
							}
						}
					);
				}
			}
		);
	};

	module.initTreatmentList = function()
	{
		$('#treatmentList').dataTable(
		{
			"aoColumnDefs":
			[
			  { "bSearchable": false, "aTargets": [ 1 ] },
			  { "bSortable": false, "aTargets": [ 1 ] }
			]
		});
		$('#treatmentList tbody').on(
			"dblclick",
			"tr",
			function(e)
			{
				var id = e.currentTarget.getAttribute('data-pk');
				window.location.href = module.routes.treatmentDetails + "/treatmentId/" + id;
				e.stopPropagation();
			}
		);
		$('#treatmentList tbody').on(
			"click",
			"button.expdesignerSelectTreatment",
			function(e)
			{
				var id = $(e.currentTarget).closest('tr').attr('data-pk');
				window.location.href = module.routes.treatmentDetails + "/treatmentId/" + id;
				e.stopPropagation();
			}
		);
		$('#treatmentList tbody').on(
			"click",
			"button.expdesignerDeleteTreatment",
			function(e)
			{
				var id = $(e.currentTarget).closest('tr').attr('data-pk');
				module.deleteTreatment(id);
				e.stopPropagation();
			}
		);
	};

	module.deleteTreatment = function(treatmentId)
	{
		bootbox.confirm(
			'Do you really want to delete this treatment?',
			function(result)
			{
				if (result)
				{
					$.ajax(
						{
							url: module.routes.deleteTreatment,
							type: 'POST',
							data: {
								treatmentId: treatmentId
							},
							cache: false,
							success: function(data, textStatus, jqXHR)
							{
								var row;

								if (module.evalXhrResult(result))
								{
									row = $('#treatmentList tbody tr[data-pk="' + treatmentId + '"]');
									if (row)
									{
										row.remove();
									}
									else
									{
										window.location.href = module.routes.treatmentList + '/experimentId/' + module.experimentId;
									}
								}
							},
							error: function()
							{
								dojo.publish('messages', [{ message: 'Deleting treatment failed', type: 'error'}]);
							}
						}
					);
				}
			}
		);
	};

	module.initTreatmentDetails = function()
	{
		var hash, m;

		dojo.require('dojo.hash');

		hash = dojo.hash();
		m = hash.match(/^tab_(.+)$/);
		if (m && dijit.byId(m[1]) && dijit.byId("treatmentDefinition").getIndexOfChild(dijit.byId(m[1])) >= 0)
		{
			dijit.byId("treatmentDefinition").selectChild(m[1]);
		}

		dojo.connect(dijit.byId("treatmentDefinition"), "selectChild", function(child)
		{
			if (child.id)
			{
				var id = "tab_" + child.id;
				dojo.hash(id);
			}
		});

		dojo.connect(dijit.byId('treatmentStructureTab'), 'onLoad', function(child){
			var hash = dojo.hash();
			if (hash.match(/^stepgroup([0-9]+)$/))
			{
				dojo.hash(hash, true);
			}
			if (module.returnToPosition)
			{
				window.document.getElementById('treatmentStructureTab').scrollTop = module.returnToPosition;
			}
			module.returnToPosition = null;
		});

		var minHeight = dojo.style("treatmentDefinition", "height");
		window.resizeDijitHeight("treatmentDefinition", 70, minHeight);
		window.addOnResize(function() { window.resizeDijitHeight("treatmentDefinition", 70, minHeight); } );
	};

	module.refreshTreatmentStructureTab = function()
	{
		module.returnToPosition = window.document.getElementById('treatmentStructureTab').scrollTop;
		dijit.byId('treatmentStructureTab').refresh();
	}

	module.initTreatmentDetailsStructure = function()
	{
		$('.treatmentStructure').sortable(
			{
				axis: 'y',
				containment: 'parent',
				items: '.stepgroupContainer',
				handle: '.stepgroupRow',
				cursor: 'move',
				delay: 150,
				update: function (event, ui)
				{
					var item, stepgroupRow, stepgroupId, position;

					item = ui.item[0];
					stepgroupRow = $('#' + item.id);
					stepgroupId = item.getAttribute('data-pk');
					// "+1": starts with "0"
					position = stepgroupRow.index() + 1;

					// TODO: move to jquery ajax and add an activity indicator or overlay
					dojo.xhrPost({
						url: '/expdesigner/treatment/modifystructure/itemType/stepgroup/itemAction/moveTo',
						content:
						{
							treatmentId: $('#treatmentStructure')[0].getAttribute('data-pk'),
							itemId: stepgroupId,
							targetPosition: position
						},
						handleAs: 'json',
						preventCache: true,
						load: function(result)
						{
							if (dojo.exists('error', result))
							{
								dojo.publish('messages', [{ message: result.error, type: "error"}]);
								module.refreshTreatmentStructureTab();
								return;
							}

							if (dojo.exists('message', result))
							{
								dojo.publish('messages', [{ message: result.message, type: "message"}]);
							}
							else
							{
								dojo.publish('messages', [{ message: 'Request succeded', type: "message"}]);
							}
						},
						error: function()
						{
							dojo.publish('messages', [{ message: 'Request failed', type: "error"}]);
							module.refreshTreatmentStructureTab();
							return;
						}
					});
				}
			}).disableSelection();

		$('.treatmentStructure .stepContainer').each(
			function(i, element)
			{
				$("#" + element.id).sortable(
				{
					axis:			'y',
					items:			'.stepRowContainer',
					handle:			'.stepRow',
					cursor:			'move',
					delay:			150,
					connectWith:	'.stepContainer',
					update: function(event, ui)
					{
						if (this !== ui.item.parent()[0])
						{
							return;
						}

						var item, stepId, stepRow, position, stepContainer, stepgroupId;

						item = ui.item[0];
						stepId = item.getAttribute('data-pk');
						stepRow = $('#' + item.id);
						position = stepRow.index() + 1;
						stepContainer = stepRow.parent()[0];
						stepgroupId = stepContainer.id.split('_')[1];

						// TODO: move to jquery ajax and add an activity indicator or overlay
						dojo.xhrPost({
							url: '/expdesigner/treatment/modifystructure/itemType/step/itemAction/moveTo',
							content:
							{
								treatmentId: $('#treatmentStructure')[0].getAttribute('data-pk'),
								itemId: stepId,
								targetStepgroupId: stepgroupId,
								targetPosition: position
							},
							handleAs: 'json',
							preventCache: true,
							load: function(result)
							{
								if (dojo.exists('error', result))
								{
									dojo.publish('messages', [{ message: result.error, type: "error"}]);
									module.refreshTreatmentStructureTab();
									return;
								}

								if (dojo.exists('message', result))
								{
									dojo.publish('messages', [{ message: result.message, type: "message"}]);
								}
								else
								{
									dojo.publish('messages', [{ message: 'Request succeded', type: "message"}]);
								}
							},
							error: function(result)
							{
								dojo.publish('messages', [{ message: 'Request failed', type: "error"}]);
								module.refreshTreatmentStructureTab();
								return;
							}
						});

						$('.treatmentStructure .stepContainer').each(
								function(i2, element2)
								{
									$(element2).find('.stepOrderCol').each(
											function(i3, element3)
											{
												$(element3).text(i3 + 1);
											}
										);
								}
							);
						}
				}).disableSelection();
			}
		);

		$('.stepgroupRow').dblclick(function(eventObject)
		{
			var elementId = eventObject.currentTarget.id.split('_')[1];
			window.location.href="/expdesigner/stepgroup/edit/stepgroupId/" + elementId;
		});

		$('.stepRow').dblclick(function(eventObject)
		{
			var elementId = eventObject.currentTarget.id.split('_')[1];
			window.location.href="/expdesigner/step/edit/stepId/" + elementId;
		});

		$('.stepgroupToggle').on('click',
			function (eventObject)
			{
				$(this).parent().next('.stepContainer2').toggle('blind');
				$(this).toggleClass('glyphicon-chevron-right');
				$(this).toggleClass('glyphicon-chevron-down');
			}
		);

		// TODO: use addStep rows only if a mouse is available
		$('.treatmentStructure .stepRow').each(
			function(i, element)
			{
				var stepId = element.id.split('_')[1];
				$("#" + element.id).after('<div id="stepAddRow_' + stepId + '_after" class="stepAddRow"><span>+</span></div>');
			}
		);

		$('.treatmentStructure .stepHeaderRow').each(
			function(i, element)
			{
				var stepgroupId = element.id.split('_')[1];
				$("#" + element.id).after('<div id="stepgroupAddRow_' + stepgroupId + '" class="stepAddRow"><span>+</span></div>');
			}
		);

		$('.treatmentStructure .stepAddRow').on('click',
			function(eventObject)
			{
				var elementData = eventObject.currentTarget.id.split('_');
				var elementType = elementData[0];
				var elementId = elementData[1];

				if (elementType === 'stepgroupAddRow')
				{
					window.location.href = module.routes.stepAdd + '/stepgroupId/' + elementId + '/position/1';
				}
				else
				{
					window.location.href = module.routes.stepAdd + '/stepId/' + elementId;
				}
			}
		);

		$('.treatmentStructure .stepgroupRow .moreOptions').each(
			function(i, element)
			{
				var menuContent, moreNavItem, moreOptions;
				moreOptions = {
					"copy":{
					  "href":"javascript:expdesigner.copyStepgroup()",
						"icon":"\/_media\/Icons\/folder_copy.png",
						"text":"Copy"
					},
					"moveToTop":{
					  "href":"javascript:expdesigner.modifyTreatmentStructure('moveStepgroupToTop')",
					  "icon":"\/_media\/sophie\/Icons\/arrow_up_double.png",
					  "text":"Move to Top"
					},
					"moveUp":{
					  "href":"javascript:expdesigner.modifyTreatmentStructure('moveStepgroupUp')",
					  "icon":"\/_media\/Icons\/arrow_up.png",
					  "text":"Move up"
					},
					"moveDown":{
					  "href":"javascript:expdesigner.modifyTreatmentStructure('moveStepgroupDown')",
					  "icon":"\/_media\/Icons\/arrow_down.png",
					  "text":"Move down"
					},
					"moveToBottom":{
					  "href":"javascript:expdesigner.modifyTreatmentStructure('moveStepgroupToBottom')",
					  "icon":"\/_media\/sophie\/Icons\/arrow_down_double.png",
					  "text":"Move to Bottom"
					}
				};

				menuContent = '<ul class="stepMoreOptionsMenu">';

				for (moreNavItem in moreOptions)
				{
					moreNavItem = moreOptions[moreNavItem];
					//console.log(moreNavItem);
					if (!moreNavItem.inactive)
					{
						menuContent += '<li>';
						menuContent += '<a href="' + moreNavItem.href + '">';
					}
					else
					{
						menuContent += '<li class="inactive">';
					}
					menuContent += '<img src="' + moreNavItem.icon + '" title="' + moreNavItem.text + '"> ' + moreNavItem.text;
					if (!moreNavItem.inactive)
					{
						menuContent += '</a>';
					}
					menuContent += '</li>';
				}

				menuContent += '</ul>';

				//console.log(element);
				$(element).popover({
					html: true,
					trigger: 'click',
					content: menuContent,
					placement: 'left',
					container: element
				});
			});

		$('.treatmentStructure .stepRow .moreOptions').each(
			function(i, element)
			{
				var moreNavItem, menuContent, moreOptions;
				moreOptions = {
					"copy":{
					  "href":"javascript:expdesigner.copyStep()",
						"icon":"\/_media\/Icons\/page_copy.png",
						"text":"Copy"
					},
					"moveToPreviousStepgroup":{
						"href":"javascript:expdesigner.modifyTreatmentStructure('moveStepToPreviousStepgroup')",
						"icon":"\/_media\/sophie\/Icons\/folder_up.png",
						"text":"Move to previous Stepgroup"
				   },
				   "moveToTop":{
					  "href":"javascript:expdesigner.modifyTreatmentStructure('moveStepToTop')",
					  "icon":"\/_media\/sophie\/Icons\/arrow_up_double.png",
					  "text":"Move to Top"
				   },
				   "moveUp":{
					  "href":"javascript:expdesigner.modifyTreatmentStructure('moveStepDown')",
					  "icon":"\/_media\/Icons\/arrow_down.png",
					  "text":"Move down"
				   },
				   "moveToBottom":{
					  "href":"javascript:expdesigner.modifyTreatmentStructure('moveStepToBottom')",
					  "icon":"\/_media\/sophie\/Icons\/arrow_down_double.png",
					  "text":"Move to Bottom"
				   },
				   "moveToNextStepgroup":{
					  "href":"javascript:expdesigner.modifyTreatmentStructure('moveStepToNextStepgroup')",
					  "icon":"\/_media\/sophie\/Icons\/folder_down.png",
					  "text":"Move to next Stepgroup"
				   }
				};

				menuContent = '<ul class="stepMoreOptionsMenu">';

				for (moreNavItem in moreOptions)
				{
					moreNavItem = moreOptions[moreNavItem];
					//console.log(moreNavItem);
					if (!moreNavItem.inactive)
					{
						menuContent += '<li>';
						menuContent += '<a href="' + moreNavItem.href + '">';
					}
					else
					{
						menuContent += '<li class="inactive">';
					}
					menuContent += '<img src="' + moreNavItem.icon + '" title="' + moreNavItem.text + '"> ' + moreNavItem.text;
					if (!moreNavItem.inactive)
					{
						menuContent += '</a>';
					}
					menuContent += '</li>';
				}

				menuContent += '</ul>';

				$(element).popover({
					html: true,
					trigger: 'click',
					content: menuContent,
					placement: 'left',
					container: element
				});
			});

		$('.stepgroupNameSpan').editable({
			type: 'text',
			name: 'name',
			url: '/expdesigner/stepgroup/set',
			title: 'Enter Stepgroup Name',
			mode: 'inline',
			onblur: 'cancel',
			params: function(params)
			{
				params.stepgroupId = params.pk;
				return params;
			}
		});

		$('.stepgroupLabelSpan').editable({
			type: 'text',
			name: 'label',
			url: '/expdesigner/stepgroup/set',
			title: 'Enter Stepgroup Label',
			mode: 'inline',
			onblur: 'cancel',
			display: function(value, sourceData)
			{
				$(this).html('Label ' + $.fn.editableutils.escape(value));
			},
			params: function(params)
			{
				params.stepgroupId = params.pk;
				return params;
			}
		});

		$('.stepgroupLoopsSpan').editable({
			type: 'text',
			name: 'loops',
			url: '/expdesigner/stepgroup/set',
			title: 'Enter Stepgroup Loops',
			mode: 'inline',
			onblur: 'cancel',
			display: function(value, sourceData)
			{
				if (value === -1)
				{
					$(this).html('Infinite Loops');
				}
				else if (value === 1)
				{
					$(this).html('1 Loop');
				}
				else
				{
					$(this).html($.fn.editableutils.escape(value) + ' Loops');
				}
			},
			params: function(params)
			{
				params.stepgroupId = params.pk;
				return params;
			}
		});

		$('.stepNameSpan').editable({
			type: 'text',
			name: 'name',
			url: '/expdesigner/step/set',
			title: 'Enter Step Name',
			mode: 'inline',
			onblur: 'cancel',
			params: function(params)
			{
				params.stepId = params.pk;
				return params;
			}
		});

	/*
		TODO: should we include this into the interface
			$('.stepSteptypeSpan').editable({
			type: 'select2',
			url: '/expdesigner/step/setsteptype',
			title: 'Select Steptype',
			select2: { width: '200px' },
			source: '/expdesigner/steptype/select/treatmentId/' + $('#treatmentStructure')[0].getAttribute('data-pk'),
			params: function(params)
			{
				params.stepId = params.pk;
				return params;
			}
		});
	*/

		$('.stepTypeSpan').editable({
			type: 'checklist',
			name: 'step_types',
			url: '/expdesigner/step/set',
			title: 'Select Participant Types',
			source: '/expdesigner/type/select/treatmentId/' + $('#treatmentStructure')[0].getAttribute('data-pk'),
			escape: true,
			sourceCache: true,
			onblur: 'cancel',
			display: function(value, sourceData)
			{
				var html = [],
				checked = $.fn.editableutils.itemsByValue(value, sourceData);
				if(checked.length)
				{
					$.each(checked, function(i, v)
					{
						html.push($.fn.editableutils.escape(v.text));
					});
					$(this).html(html.join(', '));
				}
				else
				{
					$(this).html('all');
				}
			},
			params: function(params)
			{
				params.stepId = params.pk;
				return params;
			}
		});
	};

	module.getAssets = function(treatmentId, codeMirrorId)
	{
		dojo.xhrPost({
			url: '/expdesigner/asset/getassets',
			handleAs: 'json',
			preventCache: true,
			content: {treatmentId:treatmentId, codeMirrorId:codeMirrorId},
			load: function(result)
			{
				if(dijit.byId('assetChooser'))
				{
					dijit.byId('assetChooser').show();
				}
				else{
					dojo.require('dijit.Dialog');
					var dialog = new dijit.Dialog({
						id: "assetChooser",
						title:   "Choose an asset",
						content: result.content
					});
					dialog.show();
				}
			}
		});
	};

	module.deleteStepgroup = function(stepgroupId)
	{
		bootbox.confirm(
			'Do you really want to delete this step?',
			function(result)
			{
				if (result)
				{
					dojo.xhrPost({
						url: '/expdesigner/stepgroup/delete',
						content: {stepgroupId:stepgroupId},
						handleAs: 'json',
						preventCache: true,
						load: function(result)
						{
							if (module.evalXhrResult(result))
							{
								module.refreshTreatmentStructureTab();
							}
						}
					});
				}
			}
		);
	};

	module.deleteStep = function(stepId)
	{
		bootbox.confirm(
			'Do you really want to delete this step?',
			function(result)
			{
				if (result)
				{
					dojo.xhrPost({
						url: '/expdesigner/step/delete/stepId/' + stepId,
						handleAs: 'json',
						preventCache: true,
						load: function(result) {
							if (module.evalXhrResult(result))
							{
								module.refreshTreatmentStructureTab();
							}
						}
					});
				}
			}
		);
	};

	module.deleteAsset = function(id)
	{
		bootbox.confirm(
			'Do you really want to delete this asset?',
			function(result)
			{
				if (result)
				{
					dojo.xhrPost({
						url: '/expdesigner/asset/delete/assetId/' + id,
						handleAs: 'json',
						preventCache: true,
						load: function(result)
						{
							if (module.evalXhrResult(result))
							{
								dijit.byId('treatmentAssetTab').refresh();
							}
						}
					});
				}
			}
		);
	};

	module.copyStep = function(id)
	{
		dojo.xhrPost({
			url: '/expdesigner/step/copy/stepId/' + id,
			handleAs: 'json',
			preventCache: true,
			load: function(result)
			{
				if (module.evalXhrResult(result))
				{
					module.refreshTreatmentStructureTab();
				}
			}
		});
	};

	module.copyStepgroup = function(id)
	{
		dojo.xhrPost({
			url: '/expdesigner/stepgroup/copy/stepgroupId/' + id,
			handleAs: 'json',
			preventCache: true,
			load: function(result)
			{
				if (module.evalXhrResult(result))
				{
					module.refreshTreatmentStructureTab();
				}
			}
		});
	};

	module.deleteType = function(treatmentId, typeLabel)
	{
		bootbox.confirm(
			'Do you really want to delete this type? This will also delete grouping data.',
			function(result)
			{
				if (result)
				{
					dojo.xhrPost({
						url: '/expdesigner/type/delete',
						content: {treatmentId:treatmentId, typeLabel:typeLabel},
						handleAs: 'json',
						preventCache: true,
						load: function(result)
						{
							if (module.evalXhrResult(result))
							{
								dijit.byId('treatmentParticipantsTypesTab').refresh();
							}
						}
					});
				}
			}
		);
	};

	module.modifyTreatmentStructure = function(treatmentId, itemType, itemId, itemAction)
	{
		dojo.xhrPost({
			url: '/expdesigner/treatment/modifystructure',
			content: {treatmentId:treatmentId, itemType:itemType, itemId:itemId, itemAction:itemAction},
			handleAs: 'json',
			preventCache: true,
			load: function(result)
			{
				module.refreshTreatmentStructureTab();

				if (dojo.exists('error', result))
				{
					dojo.publish('messages', [{ message: result.error, type: "error"}]);
					return;
				}

				if (dojo.exists('message', result))
				{
					dojo.publish('messages', [{ message: result.message, type: "message"}]);
				}
				else
				{
					dojo.publish('messages', [{ message: 'Request succeded', type: "message"}]);
				}
			}
		});
	};

	module.deleteSessiontype = function(id)
	{
		bootbox.confirm(
			'Do you really want to delete this sessiontype?',
			function(result)
			{
				if (result)
				{
					dojo.xhrPost({
						url: '/expdesigner/sessiontype/delete',
						content: {sessiontypeId:id},
						handleAs: 'json',
						preventCache: true,
						load: function(result)
						{
							if (module.evalXhrResult(result))
							{
								$('#sessiontypeListRow' + id).remove();
							}
						}
					});
				}
			}
		);
	};

	module.deleteTreatmentParameter = function(parameterName)
	{
		bootbox.confirm(
			'Do you really want to delete this parameter?',
			function(result)
			{
				if (result)
				{
					dojo.xhrPost({
						url: '/expdesigner/parameter/delete',
						content: {parameterName:parameterName},
						handleAs: 'json',
						preventCache: true,
						load: function(result)
						{
							if (module.evalXhrResult(result))
							{
								$('#treatmentParameterListRow' + parameterName).remove();
							}
						}
					});
				}
			}
		);
	};

	module.initTreatmentVariableList = function()
	{
		var containerId = '#treatmentVariableList';
		/*$(containerId).dataTable(
		{
			"aoColumnDefs":
			[
			  { "bSearchable": false, "aTargets": [ 1 ] },
			  { "bSortable": false, "aTargets": [ 1 ] }
			]
		});*/
		$(containerId + ' tbody').on(
			"dblclick",
			"tr",
			function(e)
			{
				var id = e.currentTarget.getAttribute('data-pk');
				window.location.href = module.routes.treatmentVariableEdit + "/treatmentId/" + module.treatmentId + "/variableId/" + id;
				e.stopPropagation();
			}
		);
		$(containerId + ' tbody').on(
			"click",
			"button.expdesignerSelectTreatmentVariable",
			function(e)
			{
				var id = $(e.currentTarget).closest('tr').attr('data-pk');
				window.location.href = module.routes.treatmentVariableEdit + "/treatmentId/" + module.treatmentId + "/variableId/" + id;
				e.stopPropagation();
			}
		);
		$(containerId + ' tbody').on(
			"click",
			"button.expdesignerDeleteTreatmentVariable",
			function(e)
			{
				var id = $(e.currentTarget).closest('tr').attr('data-pk');
				module.deleteTreatmentVariable(id);
				e.stopPropagation();
			}
		);
	};

	module.deleteTreatmentVariable = function(variableId)
	{
		bootbox.confirm(
			'Do you really want to delete this variable?',
			function(result)
			{
				if (result)
				{
					$.ajax(
						{
							url: module.routes.treatmentVariableDelete,
							type: 'POST',
							data: {
								treatmentId: module.treatmentId,
								variableId: variableId
							},
							cache: false,
							success: function(data, textStatus, jqXHR)
							{
								var row;

								if (module.evalXhrResult(result))
								{
									row = $('#treatmentVariableList tbody tr[data-pk="' + variableId + '"]');
									if (row)
									{
										row.remove();
									}
									/*else
									{
										window.location.href = module.routes.variableList + '/treatmentId/' + module.treatmentId;
									}*/
								}
							},
							error: function()
							{
								dojo.publish('messages', [{ message: 'Deleting variable failed', type: 'error'}]);
							}
						}
					);
				}
			}
		);
	};

	module.deleteAllTreatmentVariables = function()
	{
		bootbox.confirm(
			'Do you really want to delete all variables?',
			function(result)
			{
				if (result)
				{
					$.ajax(
						{
							url: module.routes.treatmentVariableDeleteall,
							type: 'POST',
							data: {
								treatmentId: module.treatmentId
							},
							cache: false,
							success: function(data, textStatus, jqXHR)
							{
								var row;

								if (module.evalXhrResult(result))
								{
									$('#treatmentVariableList tbody tr').remove();
								}
							},
							error: function()
							{
								dojo.publish('messages', [{ message: 'Deleting variable failed', type: 'error'}]);
							}
						}
					);
				}
			}
		);
	};

	module.treatmentVariableContextChange = function()
	{
		var personDropdown = $('#personContext')[0];
		var proceduralDropdown = $('#proceduralContext')[0];

		var pL = $('#formTr-participantLabel');
		var gL = $('#formTr-groupLabel');
		var sgL = $('#formTr-stepgroupLabel');
		var sglL = $('#formTr-stepgroupLoop');

		var showParticipantLabel = true;
		var showGroupLabel = true;

		var showStepgroupLabel = true;
		var showStepgroupLoop = true;

		switch (personDropdown.value)
		{
			case "e": // Everyone
				showParticipantLabel = false;
				showGroupLabel = false;
				break;
			case "g": // Group
				showParticipantLabel = false;
				showGroupLabel = true;
				break;
			case "p": // Participant
				showParticipantLabel = true;
				showGroupLabel = false;
				break;
		}

		switch (proceduralDropdown.value)
		{
			case "e": // Everywhere
				showStepgroupLabel = false;
				showStepgroupLoop = false;
				break;
			case "sg": // Stepgroup
				showStepgroupLabel = true;
				showStepgroupLoop = false;
				break;
			case "sl": // Stepgroup Loop
				showStepgroupLabel = true;
				showStepgroupLoop = true;
				break;
		}

		if (showParticipantLabel)
		{
			if (pL.hasClass('hidden'))
			{
				pL.removeClass('hidden');
			}
		}
		else
		{
			if (!pL.hasClass('hidden'))
			{
				pL.addClass('hidden');
			}
		}

		if (showGroupLabel)
		{
			if (gL.hasClass('hidden'))
			{
				gL.removeClass('hidden');
			}
		}
		else
		{
			if (!gL.hasClass('hidden'))
			{
				gL.addClass('hidden');
			}
		}

		if (showStepgroupLabel)
		{
			if (sgL.hasClass('hidden'))
			{
				sgL.removeClass('hidden');
			}
		}
		else
		{
			if (!sgL.hasClass('hidden'))
			{
				sgL.addClass('hidden');
			}
		}

		if (showStepgroupLoop)
		{
			if (sglL.hasClass('hidden'))
			{
				sglL.removeClass('hidden');
			}
		}
		else
		{
			if (!sglL.hasClass('hidden'))
			{
				sglL.addClass('hidden');
			}
		}
	};

	module.deleteTreatmentReport = function(reportId)
	{
		bootbox.confirm(
			'Do you really want to delete this report?',
			function(result)
			{
				if (result)
				{
					dojo.xhrPost({
						url: '/expdesigner/report/delete',
						content: {reportId:reportId},
						handleAs: 'json',
						preventCache: true,
						load: function(result)
						{
							if (module.evalXhrResult(result))
							{
								$('#reportListRow' + reportId).remove();
							}
						}
					});
				}
			}
		);
	};

	module.sessiontypeRegisterGroupstructure = function(label, groupstructure)
	{
		if (window.groupstructures == null) {
			window.groupstructures = {};
		}
		window.groupstructures[label] = groupstructure;
	};

	module.sessiontypeValidate = function(id, redOnly)
	{
		var groupNode = dojo.byId(id);
		var participantNodes = dojo.query('#' + id + ' >');

		var tmp = dojo.byId('sophie_temp');
		if (tmp === null) {
			tmp = dojo.create('div', { id: 'sophie_temp' }, dojo.body());
		}
		// type count
		var typeCount = {};
		var types = [];

		// sort participants
		var list = [];
		dojo.forEach(participantNodes, function(n)
		{
			list.push({
				id: dojo.attr(n, 'id'),
				sort: dojo.attr(n, 'sophieSort')
			});
			var type = dojo.attr(n, 'sophieType');
			if (typeCount[type] === undefined) {
				typeCount[type] = 0;
			}
			typeCount[type] = typeCount[type] + 1;
			types.push(type);
			dojo.place(n, tmp);
		});
		list.sort(function(a, b)
		{
			return a.sort - b.sort;
		});
		dojo.forEach(list, function(n) {
			dojo.place(dojo.byId(n.id), groupNode);
		});

		// check type count
		var typeCountOK = true;
		var struc = window.groupstructures[dojo.attr(groupNode, 'sophieGroupstructureLabel')];
		var key;
		for (key in struc) {
			if (struc.hasOwnProperty(key)) {
				if ((struc[key].min > 0 && typeCount[key] === undefined) || typeCount[key] < struc[key].min || typeCount[key] > struc[key].max) {
					typeCountOK = false;
				}
			}
		}
		dojo.require('dojo.fx');
		if (typeCountOK) {
			if (redOnly == undefined || redOnly == false) {
				var chain = new Array();
				var startColor = dojo.style(groupNode, 'backgroundColor');
				if (startColor != '#fff') {
					chain.push(dojo.animateProperty({
						node: groupNode,
						duration: 100,
						properties: {
							backgroundColor: { start: startColor, end: '#fff' }
						}
					}));
				}
				chain.push(dojo.animateProperty({
					node: groupNode,
					duration: 500,
					properties: {
						backgroundColor: { start: '#fff', end: '#48981d' }
					}
				}));
				chain.push(dojo.animateProperty({
					node: groupNode,
					duration: 500,
					properties: {
						backgroundColor: { start: '#48981d', end: '#fff' }
					}
				}));
				dojo.fx.chain(chain).play();
			}
		} else {
			dojo.fx.chain([
				dojo.animateProperty({
					node: groupNode,
					duration: 500,
					properties: {
						backgroundColor: { start: '#fff', end: '#e00' }
					}
				})
			]).play();
		}
		return typeCountOK;
	};

	module.sessiontypeValidateAll = function()
	{
		var groupNodes = dojo.query('#sessiontype td.group');
		var valid = true;
		dojo.forEach(groupNodes, function(n) {
			if (!module.sessiontypeValidate(dojo.attr(n, 'id'), false)) {
				valid = false;
			}
		});
		return valid;
	};

	module.sessiontypeCreateJson = function()
	{
		var definition = new Object();
		var groupNodes = dojo.query('#sessiontype td.group');
		dojo.forEach(groupNodes, function(n) {
			var sglLabel = dojo.attr(n, 'sophieSglLabel');
			var groupLabel = dojo.attr(n, 'sophieGroupLabel');
			if (definition[sglLabel] == undefined) {
				definition[sglLabel] = {};
			}
			if (definition[sglLabel][groupLabel] == undefined) {
				definition[sglLabel][groupLabel] = [];
			}
			var participantNodes = dojo.query('#' + dojo.attr(n, 'id') + ' >');
			dojo.forEach(participantNodes, function(p) {
				definition[sglLabel][groupLabel].push(dojo.attr(p, 'sophieId'));
			});
		});
		return dojo.toJson(definition);
	};

	module.sessiontypeSave = function(treatmentId, sessiontypeId)
	{
		if (!module.sessiontypeValidateAll()) {
			dojo.publish('messages', [{ message: 'Invalid Grouping!<br /><strong>Not saved!</strong>', type: "error"}]);
			return;
		}
		var json = module.sessiontypeCreateJson();
		dojo.xhrPost({
			url: '/expdesigner/sessiontype/grouping/treatmentId/' + treatmentId + '/sessiontypeId/' + sessiontypeId,
			content: {groupDefinitionJson: json},
			handleAs: 'json',
			preventCache: false,
			load: function(result)
			{
				module.evalXhrResult(result);
			},
			error: function(err) {
				dojo.publish('messages', [{ message: 'An error occurred!<br/>' + err.message, type: "error"}]);
			}
		});
	};

	module.treatmentClearLog = function(treatmentId)
	{
		bootbox.confirm(
			'Do you really want to delete all logs?',
			function(result)
			{
				if (result)
				{
					dojo.xhrPost({
						url: '/expdesigner/log/clear/treatmentId/' + treatmentId,
						handleAs: 'json',
						preventCache: false,
						load: function(result)
						{
							dojo.publish('messages', [{ message: result.message, type: "message"}]);
							dijit.byId('treatmentLogTab').refresh();
						},
						error: function(err) {
							dojo.publish('messages', [{ message: 'An error occurred!<br/>' + err.message, type: "error"}]);
						}
					});
				}
			}
		);
	};

	module.treatmentDisableLog = function(treatmentId)
	{
		bootbox.confirm(
			'Do you really want to disable the treatment log?',
			function(result)
			{
				if (result)
				{
					dojo.xhrPost({
						url: '/expdesigner/log/disable/treatmentId/' + treatmentId,
						handleAs: 'json',
						preventCache: false,
						load: function(result)
						{
							dojo.publish('messages', [{ message: result.message, type: "message"}]);
							dijit.byId('treatmentLogTab').refresh();
						},
						error: function(err) {
							dojo.publish('messages', [{ message: 'An error occurred!<br/>' + err.message, type: "error"}]);
						}
					});
				}
			}
		);
	};

	module.treatmentEnableLog = function(treatmentId)
	{
		bootbox.confirm(
			'Do you really want to disable the treatment log?',
			function(result)
			{
				if (result)
				{
					dojo.xhrPost({
						url: '/expdesigner/log/enable/treatmentId/' + treatmentId,
						handleAs: 'json',
						preventCache: false,
						load: function(result)
						{
							dojo.publish('messages', [{ message: result.message, type: "message"}]);
							dijit.byId('treatmentLogTab').refresh();
						},
						error: function(err) {
							dojo.publish('messages', [{ message: 'An error occurred!<br/>' + err.message, type: "error"}]);
						}
					});
				}
			}
		);
	};

	module.insertCodeExample = function(editor, type, val)
	{
		var code = '';

		switch(type)
		{

		//Variable API
			//EE
			case 'getEE':
			case 'getEEFull':
				code = '\$variableApi->getEE(\'variableName\');';
			break;

			case 'setEE':
				code = '\$variableApi->setEE(\'variableName\', \'value\');';
			break;
			case 'setEEFull':
				code = '\$variableApi->setEE(\'variableName\', \'value\', \'cast\');';
			break;

			//ES
			case 'getES':
				code = '\$variableApi->getES(\'variableName\');';
			break;

			case 'getESFull':
				code = '\$variableApi->getES(\'variableName\', \'stepgroupLabel\', \'cast\');';
			break;

			case 'setES':
				code = '\$variableApi->setES(\'variableName\', \'value\');';
			break;

			case 'setESFull':
				code = '\$variableApi->setES(\'variableName\', \'value\', \'stepgroupLabel\', \'cast\');';
			break;

			//ESL
			case 'getESL':
				code = '\$variableApi->getESL(\'variableName\');';
			break;

			case 'getESLFull':
				code = '\$variableApi->getESL(\'variableName\', \'stepgroupLabel\', \'stepgroupLoop\', \'cast\');';
			break;

			case 'setESL':
				code = '\$variableApi->setESL(\'variableName\', \'value\');';
			break;

			case 'setESLFull':
				code = '\$variableApi->setESL(\'variableName\', \'value\', \'stepgroupLabel\', \'stepgroupLoop\', \'cast\');';
			break;

			//GE
			case 'getGE':
				code = '\$variableApi->getGE(\'variableName\');';
			break;

			case 'getGEFull':
				code = '\$variableApi->getGE(\'variableName\', \'groupLabel\', \'cast\');';
			break;

			case 'setGE':
				code = '\$variableApi->setGE(\'variableName\', \'value\');';
			break;

			case 'setGEFull':
				code = '\$variableApi->setGE(\'variableName\', \'value\', \'groupLabel\', \'cast\');';
			break;

			//GS
			case 'getGS':
				code = '\$variableApi->getGS(\'variableName\');';
			break;

			case 'getGSFull':
				code = '\$variableApi->getGS(\'variableName\', \'groupLabel\', \'stepgroupLabel\', \'cast\');';
			break;

			case 'setGS':
				code = '\$variableApi->setGS(\'variableName\', \'value\');';
			break;

			case 'setGSFull':
				code = '\$variableApi->setGS(\'variableName\', \'value\', \'groupLabel\', \'stepgroupLabel\', \'cast\');';
			break;

			//GSL
			case 'getGSL':
				code = '\$variableApi->getGSL(\'variableName\');';
			break;

			case 'getGSLFull':
				code = '\$variableApi->getGSL(\'variableName\', \'groupLabel\', \'stepgroupLabel\', \'stepgroupLoop\', \'cast\');';
			break;

			case 'setGSL':
				code = '\$variableApi->setGSL(\'variableName\', \'value\');';
			break;

			case 'setGSLFull':
				code = '\$variableApi->setGSL(\'variableName\', \'value\', \'groupLabel\', \'stepgroupLabel\', \'stepgroupLoop\', \'cast\');';
			break;


			//PE
			case 'getPE':
				code = '\$variableApi->getPE(\'variableName\');';
			break;

			case 'getPEFull':
				code = '\$variableApi->getPE(\'variableName\', \'participantLabel\', \'cast\');';
			break;

			case 'setPE':
				code = '\$variableApi->setPE(\'variableName\', \'value\');';
			break;

			case 'setPEFull':
				code = '\$variableApi->setPE(\'variableName\', \'value\', \'participantLabel\', \'cast\');';
			break;

			//PS
			case 'getPS':
				code = '\$variableApi->getPS(\'variableName\');';
			break;

			case 'getPSFull':
				code = '\$variableApi->getPS(\'variableName\', \'participantLabel\', \'stepgroupLabel\', \'cast\');';
			break;

			case 'setPS':
				code = '\$variableApi->setPS(\'variableName\', \'value\');';
			break;

			case 'setPSFull':
				code = '\$variableApi->setPS(\'variableName\', \'value\', \'participantLabel\', \'stepgroupLabel\', \'cast\');';
			break;

			//PSL
			case 'getPSL':
				code = '\$variableApi->getPSL(\'variableName\');';
			break;

			case 'getPSLFull':
				code = '\$variableApi->getPSL(\'variableName\', \'participantLabel\', \'stepgroupLabel\', \'stepgroupLoop\', \'cast\');';
			break;

			case 'setPSL':
				code = '\$variableApi->setPSL(\'variableName\', \'value\');';
			break;

			case 'setPSLFull':
				code = '\$variableApi->setPSL(\'variableName\', \'value\', \'participantLabel\', \'stepgroupLabel\', \'stepgroupLoop\', \'cast\');';
			break;

		//ContextAPI
			case 'getChecksum':
				code = '\$context->getChecksum();';
			break;

			case 'getExperimentId':
				code = '\$context->getExperimentId();';
			break;

			case 'getExperiment':
				code = '\$context->getExperiment();';
			break;

			case 'getTreatmentId':
				code = '\$context->getTreatmentId();';
			break;

			case 'getTreatment':
				code = '\$context->getTreatment();';
			break;

			case 'getStepgroupLabel':
				code = '\$context->getStepgroupLabel();';
			break;

			case 'getStepgroupId':
				code = '\$context->getStepgroupId();';
			break;

			case 'getStepgroup':
				code = '\$context->getStepgroup();';
			break;

			case 'getStepgroupLoop':
				code = '\$context->getStepgroupLoop();';
			break;

			case 'getStepId':
				code = '\$context->getStepId();';
			break;

			case 'getStep':
				code = '\$context->getStep();';
			break;

			case 'getSessionId':
				code = '\$context->getSessionId();';
			break;

			case 'getSession':
				code = '\$context->getSession();';
			break;

			case 'getGroupLabel':
				code = '\$context->getGroupLabel();';
			break;

			case 'getParticipantLabel':
				code = '\$context->getParticipantLabel();';
			break;

			case 'getParticipant':
				code = '\$context->getParticipant();';
			break;

			case 'getParticipantTypeLabel':
				code = '\$context->getParticipantTypeLabel();';
			break;

		// stepApi
			case 'setRuntimeAttribute':
				code = '$stepApi->setRuntimeAttribute(\'' + val + '\', \'attribute value\');';
			break;

		// assetApi
			case 'assetInlineData':
				code = 'echo $assetApi->inlineData(\'assetLabel\');';
				break;
			case 'assetInlineImg':
				code = 'echo $assetApi->inlineImg(\'' + val + '\');';
				break;

		//HTML

			case 'table2x2':
				code = "<table>\n\t<tr><th>A</th><th>B</th></tr>\n";
				code += "\t<tr><td>C</td><td>D</td></tr>\n";
				code += "</table>";
			break;

			case 'table3x3':
				code = '<table>\n\t<tr><th>A</th><th>B</th><th>C</th></tr>\n';
				code += '\t<tr><td>D</td><td>E</td><td>F</td></tr>\n';
				code += '\t<tr><td>G</td><td>H</td><td>I</td></tr>\n';
				code += '</table>';
			break;

			case 'table4x4':
				code = '<table>\n\t<tr><th>A</th><th>B</th><th>C</th><th>D</th></tr>\n';
				code += '\t<tr><td>E</td><td>F</td><td>G</td><td>H</td></tr>\n';
				code += '\t<tr><td>I</td><td>J</td><td>K</td><td>L</td></tr>\n';
				code += '\t<tr><td>M</td><td>N</td><td>O</td><td>P</td></tr>\n';
				code += '</table>';
			break;

			case 'ul':
				code = "<ul>\n\t<li>A</li>\n\t<li>B</li>\n\t<li>C</li>\n</ul>";
			break;

			case 'ul-squares':
				code = "<ul type=\"square\">\n\t<li>A</li>\n\t<li>B</li>\n\t<li>C</li>\n</ul>";
			break;

			case 'ol':
				code = "<ol>\n\t<li>A</li>\n\t<li>B</li>\n\t<li>C</li>\n</ol>";
			break;

			case 'ol-roman':
				code = "<ol type=\"I\">\n\t<li>A</li>\n\t<li>B</li>\n\t<li>C</li>\n</ol>";
			break;

			case 'if':
				code = "if($variable == true){\n\n}";
			break;

			case 'if-else':
				code = "if($variable == true){\n\n}else{\n\n}";
			break;

			case 'if-elseif-else':
				code = "if($variable == true){\n\n}elseif($variable2 == true){\n\n}else{\n\n}";
			break;

			case 'for':
				code = "for($i = 0; $i < $variable == true; $i++){\n\n}";
			break;

			case 'foreach':
				code = "foreach($array as $item){\n\n}";
			break;

			case 'switch':
				code = "switch($variable){\n\tcase 'var1':\n\tbreak;\n\n\tcase 'var2':\n\tbreak;\n}";
			break;
		}
		return editor.replaceSelection(code);
	};

	module.updateStepCodeSanitizerResults = function(stepId, code, messageTarget, type)
	{
		var limitedCall = function () {
			dojo.xhrPost({
				url: module.routes.stepCheckcode,
				content: {stepId:stepId, code:code, type:type},
				handleAs: 'json',
				preventCache: true,
				load: function(result)
				{
					var messageContent = '';

					if (result.hasOwnProperty('errors'))
					{
						$.each(result.errors, function(i, error)
						{
							if (messageContent !== '')
							{
								messageContent += '<br />';
							}
							messageContent += error;
							//messageContent = messageContent + ' in line ' + error.line;
						});

						if (messageContent != '')
						{
							messageContent = '<strong>Sanitizer Warning</strong><br />' + messageContent;
						}
					}

					if (result.hasOwnProperty('notices'))
					{
						var noticeText = '';
						$.each(result.notices, function(i, notice)
						{
							if (noticeText != '')
							{
								noticeText += '<br />';
							}
							noticeText += notice;
						});

						if (noticeText != '')
						{
							if (messageContent != '')
							{
								messageContent += '<br />';
							}
							messageContent += '<strong>Notices</strong><br />' + noticeText;
						}
					}

					var messageTargetElement = $('#' + messageTarget);
					if (messageContent != '')
					{
						messageTargetElement.html(messageContent);
						messageTargetElement.css('display', 'block');
					}
					else
					{
						messageTargetElement.html('');
						messageTargetElement.css('display', 'none');
					}
				}
			});
		};

		rateLimitCall('call-/expdesigner/step/checkcode-for' + stepId, limitedCall, 2500);
	};

	module.updateTreatmentReportDefinitionSanitizerResults = function(treatmentId, code, messageTarget)
	{
		var limitedCall = function () {
			dojo.xhrPost({
				url: module.routes.reportCheckcode,
				content: {treatmentId:treatmentId, code:code},
				handleAs: 'json',
				preventCache: true,
				load: function(result)
				{

					var messageContent = '';

					if (result.hasOwnProperty('errors'))
					{
						$.each(result.errors, function(i, error)
						{
							if (messageContent != '')
							{
								messageContent += '<br />';
							}
							messageContent += error;
						});

						if (messageContent !== '')
						{
							messageContent = '<strong>Sanitizer Warning</strong><br />' + messageContent;
						}
					}

					if (messageContent !== '')
					{
						$('#' + messageTarget).html(messageContent);
						$('#' + messageTarget).css('display', 'block');
					}
					else
					{
						$('#' + messageTarget).css('display', 'none');
					}
				}
			});
		};

		rateLimitCall('call-/expdesigner/report/checkcode-for' + treatmentId, limitedCall, 2500);
	};

	module.updateTreatmentPayoffScriptSanitizerResults = function(treatmentId, code, messageTarget)
	{
		var limitedCall = function () {
			dojo.xhrPost({
				url: module.routes.treatmentCheckpayoffscriptcode,
				content: {treatmentId:treatmentId, code:code},
				handleAs: 'json',
				preventCache: true,
				load: function(result)
				{

					var messageContent = '';

					if (result.hasOwnProperty('errors'))
					{
						$.each(result.errors, function(i, error)
						{
							if (messageContent != '')
							{
								messageContent += '<br />';
							}
							messageContent += error;
						});

						if (messageContent !== '')
						{
							messageContent = '<strong>Sanitizer Warning</strong><br />' + messageContent;
						}
					}

					if (messageContent !== '')
					{
						$('#' + messageTarget).html(messageContent);
						$('#' + messageTarget).css('display', 'block');
					}
					else
					{
						$('#' + messageTarget).css('display', 'none');
					}
				}
			});
		};

		rateLimitCall('call-/expdesigner/treatment/checkpayoffscriptcode-' + messageTarget + '-for' + treatmentId, limitedCall, 2500);
	};

	module.updateTreatmentSetupScriptSanitizerResults = function(treatmentId, code, messageTarget)
	{
		var limitedCall = function () {
			dojo.xhrPost({
				url: module.routes.treatmentChecksetupscriptcode,
				content: {treatmentId:treatmentId, code:code},
				handleAs: 'json',
				preventCache: true,
				load: function(result)
				{

					var messageContent = '';

					if (result.hasOwnProperty('errors'))
					{
						$.each(result.errors, function(i, error)
						{
							if (messageContent != '')
							{
								messageContent += '<br />';
							}
							messageContent += error;
						});

						if (messageContent !== '')
						{
							messageContent = '<strong>Sanitizer Warning</strong><br />' + messageContent;
						}
					}

					if (messageContent !== '')
					{
						$('#' + messageTarget).html(messageContent);
						$('#' + messageTarget).css('display', 'block');
					}
					else
					{
						$('#' + messageTarget).css('display', 'none');
					}
				}
			});
		};

		rateLimitCall('call-/expdesigner/treatment/checksetupscriptcode-for' + treatmentId, limitedCall, 2500);
	};

	module.previewFrameLoaded = function(frame)
	{
		var newHeight = parseInt(frame.contentDocument.body.scrollHeight, 10) + 10;
		if (newHeight > 400)
		{
			frame.style.height = newHeight + 'px';
		}
	};

	module.initStepTabContainer = function()
	{
		var tabContainer, hash, m;

		dojo.require('dojo.hash');

		tabContainer = dijit.byId("adminForm-TabContainer");
		dojo.connect(tabContainer, "selectChild", function(childId)
		{
			if (childId.id)
			{
				childId = childId.id;
			}

//			$('#adminForm-TabContainer').height($('#' + childId).height() + 80);

			dojo.byId("__tabAnchor").value = "tab_" + childId;
			dojo.hash("tab_" + childId);
		});

		dojo.addOnLoad(function()
		{
			//$('#adminForm-TabContainer').height($('#' + tabContainer.selectedChildWidget.containerNode.id).height() + 150);

			hash = dojo.hash();
			m = hash.match(/^tab_(.+)$/);
			if (m && dijit.byId(m[1]) && tabContainer.getIndexOfChild(dijit.byId(m[1])) >= 0)
			{
				try { dojo.byId("__tabAnchor").value=hash; } catch (e) { console.log(e); }
				dijit.byId("adminForm-TabContainer").selectChild(m[1]);
			}

		});

		dojo.subscribe("/dojo/hashchange", null,
		function()
		{
			var tabContainer, m;
			tabContainer = dijit.byId("adminForm-TabContainer");
			m = dojo.hash().match(/^tab_(.+)$/);
			if (m && dijit.byId(m[1]) && tabContainer.getIndexOfChild(dijit.byId(m[1])) >= 0)
			{
				tabContainer.selectChild(m[1]);
			}
		});
	};

	module.initTreatmentTypeList = function()
	{
		var containerId = '#treatmentTypeList';
		/*$(containerId).dataTable(
		{
			"aoColumnDefs":
			[
			  { "bSearchable": false, "aTargets": [ 1 ] },
			  { "bSortable": false, "aTargets": [ 1 ] }
			]
		});*/
		/*
		$(containerId + ' tbody').on(
			"dblclick",
			"tr",
			function(e)
			{
				var id = e.currentTarget.getAttribute('data-pk');
				window.location.href = module.routes.treatmentVariableEdit + "/variableId/" + id;
				e.stopPropagation();
			}
		);
		$(containerId + ' tbody').on(
			"click",
			"button.expdesignerSelectTreatmentVariable",
			function(e)
			{
				var id = $(e.currentTarget).closest('tr').attr('data-pk');
				window.location.href = module.routes.treatmentVariableEdit + "/variableId/" + id;
				e.stopPropagation();
			}
		);
		$(containerId + ' tbody').on(
			"click",
			"button.expdesignerDeleteTreatmentVariable",
			function(e)
			{
				var id = $(e.currentTarget).closest('tr').attr('data-pk');
				module.deleteTreatmentVariable(id);
				e.stopPropagation();
			}
		);*/
	};

	module.initTreatmentAssetList = function()
	{
		var containerId = '#treatmentAssetList';
		/*$(containerId).dataTable(
		{
			"aoColumnDefs":
			[
			  { "bSearchable": false, "aTargets": [ 1 ] },
			  { "bSortable": false, "aTargets": [ 1 ] }
			]
		});
		$(containerId + ' tbody').on(
			"dblclick",
			"tr",
			function(e)
			{
				var id = e.currentTarget.getAttribute('data-pk');
				window.location.href = module.routes.treatmentVariableEdit + "/variableId/" + id;
				e.stopPropagation();
			}
		);
		$(containerId + ' tbody').on(
			"click",
			"button.expdesignerSelectTreatmentVariable",
			function(e)
			{
				var id = $(e.currentTarget).closest('tr').attr('data-pk');
				window.location.href = module.routes.treatmentVariableEdit + "/variableId/" + id;
				e.stopPropagation();
			}
		);
		$(containerId + ' tbody').on(
			"click",
			"button.expdesignerDeleteTreatmentVariable",
			function(e)
			{
				var id = $(e.currentTarget).closest('tr').attr('data-pk');
				module.deleteTreatmentVariable(id);
				e.stopPropagation();
			}
		);*/
	};

	module.deleteTreatmentAsset = function(id)
	{
	/*
		bootbox.confirm(
			'Do you really want to delete this variable?',
			function(result)
			{
				if (result)
				{
					dojo.xhrPost({
						url: module.routes.deleteTreatmentVariable,
						content: {variableId:id},
						handleAs: 'json',
						preventCache: true,
						load: function(result) {
							if (module.evalXhrResult(result))
							{
								$('#treatmentVariableList tbody tr[data-pk="' + id + '"]').remove();
							}
						}
					});
				}
			}
		);
		*/
	};

	module.initTreatmentReportList = function()
	{
		var containerId = '#treatmentReportList';
		/*$(containerId).dataTable(
		{
			"aoColumnDefs":
			[
			  { "bSearchable": false, "aTargets": [ 1 ] },
			  { "bSortable": false, "aTargets": [ 1 ] }
			]
		});
		$(containerId + ' tbody').on(
			"dblclick",
			"tr",
			function(e)
			{
				var id = e.currentTarget.getAttribute('data-pk');
				window.location.href = module.routes.treatmentVariableEdit + "/variableId/" + id;
				e.stopPropagation();
			}
		);
		$(containerId + ' tbody').on(
			"click",
			"button.expdesignerSelectTreatmentVariable",
			function(e)
			{
				var id = $(e.currentTarget).closest('tr').attr('data-pk');
				window.location.href = module.routes.treatmentVariableEdit + "/variableId/" + id;
				e.stopPropagation();
			}
		);
		$(containerId + ' tbody').on(
			"click",
			"button.expdesignerDeleteTreatmentVariable",
			function(e)
			{
				var id = $(e.currentTarget).closest('tr').attr('data-pk');
				module.deleteTreatmentVariable(id);
				e.stopPropagation();
			}
		);*/
	};

	module.deleteTreatmentReport = function(id)
	{
	/*
		bootbox.confirm(
			'Do you really want to delete this report?',
			function(result)
			{
				if (result)
				{
					dojo.xhrPost({
						url: module.routes.deleteTreatmentVariable,
						content: {variableId:id},
						handleAs: 'json',
						preventCache: true,
						load: function(result) {
							if (module.evalXhrResult(result))
							{
								$('#treatmentVariableList tbody tr[data-pk="' + id + '"]').remove();
							}
						}
					});
				}
			}
		);*/
	};

	window.expdesigner = module;

}) ( window, window.jQuery, window.dojo, window.dijit, window.bootbox, window.console, window.rateLimitCall);