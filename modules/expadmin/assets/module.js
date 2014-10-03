(function( window, $, _, dojo, dijit, bootbox, Backbone, console, Math, Date, undefined)
{
	var module						= {},
		baseUrl 					= '/';

	module.name						= 'expadmin';
	module.baseUrl					= baseUrl + module.name;

	module.sessionId				= null;

	module.openDebugServerHost		= null;
	module.openDebugUrl				= null;
	module.openDebugProtocol		= null;

	module.sessionDetails = {
		lastLogId:					null,
		timerRunning:				true,
		adminProcessRunning:		'stopped'
	};

	module.models = {
		experiment:					null,
		treatment:					null,
		stepgroups:					null,
		steps:						null,
		types:						null,

		session:					null,
		participants:				null,
		logs:						null
	};

	module.routes = {
		sessionList:					module.baseUrl,
		sessionDetails:					module.baseUrl + '/session/details',
		sessionDetailsdata:				module.baseUrl + '/session/detailsdata',
		sessionSetstate:				module.baseUrl + '/session/setstate',
		sessionSet:						module.baseUrl + '/session/set',
		sessionDelete:					module.baseUrl + '/session/delete',
		sessionDeleted:					module.baseUrl + '/session/deleted',

		optionsAdminsync:				module.baseUrl + '/options/adminsync',
		optionsTimer:					module.baseUrl + '/options/timer',
		optionsTimerset:				module.baseUrl + '/options/timerset',

		participantAdd:					module.baseUrl + '/participant/add',
		participantEditall:				module.baseUrl + '/participant/editall',
		participantEdittype:			module.baseUrl + '/expadmin/participant/edittype',

		groupAdd:						module.baseUrl + '/group/add',

		variableImport:					module.baseUrl + '/variable/import',
		variableAdd:					module.baseUrl + '/variable/add',

		logClear:						module.baseUrl + '/log/clear',

		debugTreatmentcacheclear:		module.baseUrl + '/debug/treatmentcacheclear',
		debugTreatmentcacheprefill:		module.baseUrl + '/debug/treatmentcacheprefill',
		debugDeletesync:				module.baseUrl + '/debug/deletesync',

		process:						module.baseUrl + '/process/index'
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

	module.initSessionList = function()
	{
		$('#sessionList').dataTable(
		{
			"aoColumnDefs":
			[
			  { "bSearchable": false, "aTargets": [ 5 ] },
			  { "bSortable": false, "aTargets": [ 5 ] }
			]
		});
		$('#sessionList tbody').on(
			"dblclick",
			"tr",
			function(e)
			{
				window.location.href=  "/expadmin/session/details/sessionId/" + e.currentTarget.getAttribute('data-pk');
				e.stopPropagation();
			}
		);
	};

	module.deleteSession = function(id)
	{
		bootbox.confirm(
			'Do you really want to delete this session?',
			function(result)
			{
				if (result)
				{
					$.ajax(
						{
							url: module.routes.sessionDelete,
							type: 'POST',
							data: {
								sessionId:	id
							},
							cache: false,
							success: function(data, textStatus, jqXHR)
							{
								var row;

								if (module.evalXhrResult(result))
								{
									row = $('#sessionListRow' + id);
									if (row)
									{
										row.remove();
									}
									else
									{
										window.location.href = module.routes.sessionList;
									}
								}
							},
							error: function()
							{
								dojo.publish('messages', [{ message: 'Deleting session failed', type: 'error'}]);
							}
						}
					);
				}
			}
		);
	};

	module.initSessionDetails = function(sessionId)
	{
		module.sessionId = sessionId;

		module.sessionDetails.sessionStateElement = $('#sessionState');

		module.sessionDetails.sessionParticipantsBackwardestStepElement = $('#sessionParticipantsBackwardestStep');
		module.sessionDetails.sessionParticipantsFurthestStepElement = $('#sessionParticipantsFurthestStep');
		module.sessionDetails.sessionParticipantsFurthestContactElement = $('#sessionParticipantsFurthestContact');

		module.sessionDetails.sessionAdminProcessStateElement = $('#sessionAdminProcessState');
		module.sessionDetails.sessionAdminProcessLastElement = $('#sessionAdminProcessLast');

		module.sessionDetails.logContainerElement = $('#sessionRecentLogMessagesContainer');
		module.sessionDetails.recentLogMessagesContainerElement = $('#sessionRecentLogMessages');

		module.sessionDetails.setAdminProcessRunningElement = $('#sessionSetAdminProcessStateRunning');
		module.sessionDetails.setAdminProcessStoppedElement = $('#sessionSetAdminProcessStateStopped');

		module.sessionDetails.sessionSetStateRunning = $('#sessionSetStateRunning');
		module.sessionDetails.sessionSetStatePaused = $('#sessionSetStatePaused');

		module.sessionDetails.sessionmonitorElement = $('#sessionmonitor');
		module.sessionDetails.smloop___newElement = $('#smloop___new');
		module.sessionDetails.smloop___wait_for_startElement = $('#smloop___wait_for_start');
		module.sessionDetails.smloop___finishedElement = $('#smloop___finished');
		module.sessionDetails.smloop___undefinedElement = $('#smloop___undefined');
		module.sessionDetails.smloop___excludedElement = $('#smloop___excluded');

		module.sessionDetails.smparticipants___newElement = $('#smparticipants___new');
		module.sessionDetails.smparticipants___wait_for_startElement = $('#smparticipants___wait_for_start');
		module.sessionDetails.smparticipants___finishedElement = $('#smparticipants___finished');
		module.sessionDetails.smparticipants___undefinedElement = $('#smparticipants___undefined');
		module.sessionDetails.smparticipants___excludedElement = $('#smparticipants___excluded');

		module.sessionDetails.tabContainer = dijit.byId('sessionDetailsTabContainer');

		// implement hashchange listener to select tab accordingly
		dojo.subscribe('/dojo/hashchange', null, function()
		{
			if (dojo.hash() !== '')
			{
				dijit.byId('sessionDetailsTabContainer').selectChild(dojo.hash());
			}
			else
			{
				dijit.byId('sessionDetailsTabContainer').selectChild('sessionParticipantsTab');
			}
		});

		dojo.connect(module.sessionDetails.tabContainer, 'selectChild', function(child)
		{
			try
			{
				dojo.hash(child.id);
			}
			catch (e)
			{
				console.log(e);
			}
		});

		if (dojo.hash() !== '')
		{
			module.sessionDetails.tabContainer.selectChild(dojo.hash());
		}

		module.models.experiment = new Backbone.Model();
		module.models.experiment.on('change', module.experimentChange);

		module.models.treatment = new Backbone.Model();
		module.models.treatment.on('change', module.treatmentChange);

		module.models.sessiontype = new Backbone.Model();
		module.models.sessiontype.on('change', module.sessiontypeChange);

		module.models.stepgroups = new Backbone.Collection();
		module.models.stepgroups.on('add', module.stepgroupAdd);
		module.models.stepgroups.on('change', module.stepgroupChange);
		module.models.stepgroups.on('remove', module.stepgroupRemove);

		module.models.steps = new Backbone.Collection();
		module.models.steps.on('add', module.stepAdd);
		module.models.steps.on('change', module.stepChange);
		module.models.steps.on('remove', module.stepRemove);

		module.models.types = new Backbone.Collection();
		module.models.types.on('add', module.typeAdd);
		module.models.types.on('change', module.typeChange);
		module.models.types.on('remove', module.typeRemove);

		module.models.session = new Backbone.Model();
		module.models.session.on('change', module.sessionChange);
		module.models.session.on('change:state', module.sessionChangeState);

		module.models.participants = new Backbone.Collection();
		module.models.participants.on('add', module.participantAdd);
		module.models.participants.on('change', module.participantChange);
		module.models.participants.on('remove', module.participantRemove);
		module.models.participants.on('change:state change:stepgroupId change:stepgroupLoop change:stepId', module.participantChangeProcess);
		module.models.participants.on('change:lastContact', module.participantChangeLastContact);

		module.models.sessionParticipants = new Backbone.Model();
		module.models.sessionParticipants.on('change', module.sessionParticipantsChange);

		module.models.logs = new Backbone.Collection();
		module.models.logs.on('add', module.logAdd);
		module.models.logs.on('remove', module.logRemove);

		var minHeight = dojo.style("sessionDetailsMain", "height");
		window.resizeDijitHeight("sessionDetailsMain", 70, minHeight);
		window.addOnResize(function() { window.resizeDijitHeight("sessionDetailsMain", 70, minHeight); } );

		module.loadSessionDetails();
	};

	module.loadSessionDetails = function()
	{
		$.ajax(
			{
				url:	module.routes.sessionDetailsdata,
				type:	'POST',
				data:	{ sessionId: module.sessionId },
				cache:	false,
				success: function(data, textStatus, jqXHR)
				{
					module.models.experiment.set(data.experiment);
					module.models.treatment.set(data.treatment);
					if (data.hasOwnProperty('sessiontype'))
					{
						module.models.sessiontype.set(data.sessiontype);
					}
					module.models.stepgroups.set(data.stepgroups);
					module.models.steps.set(data.steps);
					module.models.types.set(data.types);

					module.models.session.set(data.session);

					module.sessionProcess();
					module.participantCheckLastContact();
				},
				error: function()
				{
					dojo.publish('messages', [{ message: 'Session details could not be loaded. Will retry in 5 seconds', type: "error"}]);

					window.setTimeout(module.loadSessionDetails, 5000);
				}
			}
		);
	};

	module.sessionProcess = function()
	{
		$.ajax({
				url:	module.routes.process,
				type:	'POST',
				data:	{
					sessionId: 			module.sessionId,
					adminProcessState:	module.sessionDetails.adminProcessState,
					lastLogId:			module.sessionDetails.lastLogId
				},
				cache: false,

				success: function(data, textStatus, jqXHR)
				{
					if (data.hasOwnProperty('error'))
					{
						dojo.publish('messages', [{ message: 'Polling the session state failed: ' + data.error + ' Will retry in 5 Seconds.', type: "error"}]);

						if (module.sessionDetails.timerRunning)
						{
							window.setTimeout(module.sessionProcess, 5000);
						}
						return;
					}

					// process response
					if (data.hasOwnProperty('session'))
					{
						module.models.session.set(data.session);
					}

					if (data.hasOwnProperty('sessionParticipants'))
					{
						module.models.sessionParticipants.set(data.sessionParticipants);
					}

					if (data.hasOwnProperty('participants'))
					{
						module.models.participants.set(data.participants);
					}

					if (data.hasOwnProperty('logs'))
					{
						module.models.logs.add(data.logs);
					}

					// start again after some time
					if (module.sessionDetails.timerRunning)
					{
						window.setTimeout(module.sessionProcess, 1500);
					}
				},

				error: function()
				{
					dojo.publish('messages', [{ message: 'Polling the session state failed. Will retry in 5 Seconds.', type: "error"}]);

					if (module.sessionDetails.timerRunning)
					{
						window.setTimeout(module.sessionProcess, 5000);
					}
				}
			}
		);
	};

	module.setAdminProcessState = function(state)
	{
		module.sessionDetails.adminProcessState = state;

		if (state === 'running')
		{
			module.sessionDetails.setAdminProcessRunningElement.addClass('hidden');
			module.sessionDetails.setAdminProcessStoppedElement.removeClass('hidden');
			module.sessionDetails.sessionAdminProcessStateElement.html('<img src="/_media/ajax-loader.gif"> running');

			dojo.publish('messages', [{ message: 'Admin process set to running', type: "message"}]);
		}
		else if (state === 'stopped')
		{
			module.sessionDetails.setAdminProcessRunningElement.removeClass('hidden');
			module.sessionDetails.setAdminProcessStoppedElement.addClass('hidden');
			module.sessionDetails.sessionAdminProcessStateElement.html('not running');

			dojo.publish('messages', [{ message: 'Admin process stopped', type: "message"}]);
		}
	};

	module.sessionSetState = function(state)
	{
		module.models.session.set('state', state);

		$.ajax(
			{
				url: module.routes.sessionSetstate,
				type: 'POST',
				data: {
					sessionId: 	module.sessionId,
					state:		state
				},
				cache: false,
				success: function(data, textStatus, jqXHR)
				{
					if (data.hasOwnProperty('error'))
					{
						dojo.publish('messages', [{ message: data.error, type: "error"}]);
						return;
					}

					if (state === 'running')
					{
						dojo.publish('messages', [{ message: 'Session state set to running', type: "message"}]);
					}
					else if (state === 'paused')
					{
						dojo.publish('messages', [{ message: 'Session state set to paused', type: "message"}]);
					}

					if (data.hasOwnProperty('message'))
					{
						dojo.publish('messages', [{ message: data.message, type: "message"}]);
					}
					else
					{
						dojo.publish('messages', [{ message: 'Request succeded', type: "message"}]);
					}
				},
				error: function()
				{
					dojo.publish('messages', [{ message: 'An error occurred!', type: "error"}]);
				}
			}
		);
	};

	module.experimentChange = function(model, collection, options)
	{
		//console.log('Experiment changed');
	};

	module.treatmentChange = function(model, collection, options)
	{
		//console.log('Treatment changed');
	};

	module.sessiontypeChange = function(model, collection, options)
	{
		//console.log('Sessiontype changed');
	};

	module.stepgroupAdd = function(model, collection, options)
	{
		//console.log('Stepgroup added');
	};

	module.stepgroupChange	= function(model, collection, options)
	{
		//console.log('Stepgroup changed');
	};

	module.stepgroupRemove	= function(model, collection, options)
	{
		//console.log('Stepgroup removed');
	};

	module.stepAdd = function(model, collection, options)
	{
		//console.log('Step added');
	};

	module.stepChange = function(model, collection, options)
	{
		//console.log('Step changed');
	};

	module.stepRemove = function(model, collection, options)
	{
		//console.log('Step removed');
	};

	module.typeAdd = function(model, collection, options)
	{
		//console.log('Type added');
	};

	module.typeChange = function(model, collection, options)
	{
		//console.log('Type changed');
	};

	module.typeRemove = function(model, collection, options)
	{
		//console.log('Type removed');
	};

	module.sessionChange = function(model, collection, options)
	{
		//console.log('Session changed');
		module.sessionDetails.sessionAdminProcessLastElement.text(model.get('lastAdminProcess'));
	};

	module.sessionChangeState = function(model, collection, options)
	{
		var state = model.get('state');
		module.sessionDetails.sessionStateElement.text(state);
		if (state === 'deleted')
		{
			window.location.href = module.routes.sessionDeleted;
		}
		else if (state === 'running')
		{
			module.sessionDetails.sessionSetStateRunning.addClass('hidden');
			module.sessionDetails.sessionSetStatePaused.removeClass('hidden');
		}
		else if (state === 'paused')
		{
			module.sessionDetails.sessionSetStateRunning.removeClass('hidden');
			module.sessionDetails.sessionSetStatePaused.addClass('hidden');
		}
	};

	module.updateStepgroupLoopContainerVisibility = function()
	{
		var stepgroupLoopContainers, stepgroupLoopContainersEntry, stepgroupLoopContainerParticipants, i;

		stepgroupLoopContainers = $('#' + module.sessionDetails.sessionmonitorElement[0].id + ' tbody').get();

		for (i = 0; i < stepgroupLoopContainers.length; i++)
		{
			stepgroupLoopContainerParticipants = $('#' + stepgroupLoopContainers[i].id + ' .participant');
			stepgroupLoopContainersEntry = $('#' + stepgroupLoopContainers[i].id);

			//console.log( stepgroupLoopContainers[i].id + ' ' + stepgroupLoopContainerParticipants.length);

			if (stepgroupLoopContainerParticipants.length === 0 && !stepgroupLoopContainersEntry.hasClass('hidden'))
			{
				stepgroupLoopContainersEntry.addClass('hidden');
			}
			else if (stepgroupLoopContainerParticipants.length > 0 && stepgroupLoopContainersEntry.hasClass('hidden'))
			{
				stepgroupLoopContainersEntry.removeClass('hidden');
			}
		}
	};

	module.addStepgroupLoopContainer = function(stepgroupLabel, stepgroupLoop)
	{
		var stepgroup, steps, stepgroupLoopContainer, stepContainer, firstStep, stepgroupLoopLeadCol, stepCol, participantsCol, optionsCol, optionsTimerButton, optionsSyncButton, stepgroupLoopContainers, i, stepgroupLoopContainersEntry;

		stepgroup = module.models.stepgroups.findWhere({label: stepgroupLabel});

		if (!stepgroup)
		{
			console.log('Stepgroup does not exist -> reload session details');
			module.loadSessionDetails();
			return false;
		}
		//console.log(stepgroup);

		steps = module.models.steps.where({stepgroupId: stepgroup.get('id')});
		if (steps.length === 0)
		{
			console.log('Stepgroup does not contain steps');
			module.loadSessionDetails();
			return false;
		}
		//console.log(steps);

		stepgroupLoopContainer = $('<tbody />');
		stepgroupLoopContainer.addClass('stepgroupLoopContainer');
		stepgroupLoopContainer.addClass('hidden');
		stepgroupLoopContainer.attr('id', 'smloop_' + stepgroupLabel + '_' + stepgroupLoop);
		stepgroupLoopContainer.attr('data-stepgroup-position', stepgroup.get('position'));
		stepgroupLoopContainer.attr('data-stepgroup-loop', stepgroupLoop);

		firstStep = true;
		_.forEach(steps, function(step)
		{
			stepContainer = $('<tr />');
			stepContainer.attr('id', 'smrow_' + stepgroupLabel + '_' + stepgroupLoop + '_' + step.get('id'));

			if (firstStep)
			{
				stepgroupLoopLeadCol = $('<th />');
				stepgroupLoopLeadCol.attr('rowspan', steps.length);
				stepgroupLoopLeadCol.text(stepgroupLabel + '.' + stepgroupLoop);
				stepgroupLoopLeadCol.appendTo(stepContainer);
			}

			stepCol = $('<td />');
			stepCol.text(step.get('name'));
			stepCol.appendTo(stepContainer);

			participantsCol = $('<td />');
			participantsCol.addClass('participants');
			participantsCol.attr('id', 'smparticipants_' + stepgroupLabel + '_' + stepgroupLoop + '_' + step.get('id'));
			participantsCol.appendTo(stepContainer);

			optionsCol = $('<td />');
			if (step.get('timerEnabled') === '1')
			{
				optionsTimerButton = $('<button />');
				optionsTimerButton.addClass('btn');
				optionsTimerButton.addClass('btn-default');
				optionsTimerButton.addClass('btn-xs');
				optionsTimerButton.html('<img src="/_media/Icons/clock.png" />');
				optionsTimerButton.on('click', function()
				{
					var myWin, winUrl;
					winUrl = module.routes.optionsTimer + '/sessionId/' + module.sessionId + '/stepId/' + step.get('id') + '/stepgroupLoop/' + stepgroupLoop + '/popup/1';
					myWin = window.open(winUrl, "Session Administration - Step Timer",					"width=300,height=400,left=100,top=200");
					myWin.focus();

					/*bootbox.dialog({
					  message: "I am a custom dialog",
					  title: "Custom title",
					  buttons: {
						success: {
						  label: "Success!",
						  className: "btn-success",
						  callback: function() {
							alert('callback');
						  }
						},
						danger: {
						  label: "Danger!",
						  className: "btn-danger",
						  callback: function() {
							alert('callback2');
						  }
						},
						main: {
						  label: "Click ME!",
						  className: "btn-primary",
						  callback: function() {
							alert('callback3');
						  }
						}
					  }
					});*/
				});
				optionsTimerButton.appendTo(optionsCol);
			}

			if (step.get('steptypeSystemName').substr(0, 27) === 'Sophie_Steptype_Sync_Admin_')
			{
				optionsSyncButton = $('<button />');
				optionsSyncButton.addClass('btn');
				optionsSyncButton.addClass('btn-default');
				optionsSyncButton.addClass('btn-xs');
				optionsSyncButton.html('<img src="/_media/Icons/arrow_switch.png" />');
				optionsSyncButton.on( 'click', function()
				{
					var myWin, winUrl;
					winUrl = module.routes.optionsAdminsync + '/sessionId/' + module.sessionId + '/stepId/' + step.get('id') + '/stepgroupLoop/' + stepgroupLoop + '/popup/1';
					myWin = window.open(winUrl, "Session Administration - Step Synchronization",					"width=300,height=400,left=100,top=200");
					myWin.focus();
				});
				optionsSyncButton.appendTo(optionsCol);
			}
			optionsCol.appendTo(stepContainer);

			stepContainer.appendTo(stepgroupLoopContainer);
			firstStep = false;
		});

		stepgroupLoopContainers = $('#' + module.sessionDetails.sessionmonitorElement[0].id + ' tbody.stepgroupLoopContainer').get();

		for (i = 0; i < stepgroupLoopContainers.length; i++)
		{
			stepgroupLoopContainersEntry = $('#' + stepgroupLoopContainers[i].id);

			//console.log(stepgroup.get('position') + ' > ' + parseInt(stepgroupLoopContainersEntry.attr('data-stepgroup-position')));
			if (stepgroup.get('position') > parseInt(stepgroupLoopContainersEntry.attr('data-stepgroup-position')))
			{
				continue;
			}

			//console.log(stepgroupLoop + ' > ' + parseInt(stepgroupLoopContainersEntry.attr('data-stepgroup-loop')));

			if (stepgroup.get('position') == parseInt(stepgroupLoopContainersEntry.attr('data-stepgroup-position')) && stepgroupLoop > parseInt(stepgroupLoopContainersEntry.attr('data-stepgroup-loop')))
			{
				continue;
			}

			stepgroupLoopContainer.insertBefore(stepgroupLoopContainersEntry);
			return true;
		}

		stepgroupLoopContainer.insertBefore(module.sessionDetails.smloop___finishedElement);
		return true;
	};

	module.getParticipantContainer = function(model)
	{
		var container;

		if (model.get('state') === 'new')
		{
			//console.log('getParticipantContainer: new');
			return module.sessionDetails.smparticipants___newElement;
		}

		if (model.get('state') === 'finished')
		{
			//console.log('getParticipantContainer: finished');
			return module.sessionDetails.smparticipants___finishedElement;
		}

		if (model.get('state') === 'excluded')
		{
			//console.log('getParticipantContainer: excluded');
			return module.sessionDetails.smparticipants___excludedElement;
		}

		if (!model.has('stepgroupLabel') || !model.has('stepgroupLoop') || !model.has('stepId'))
		{
			//console.log('getParticipantContainer: attribute missing');
			return module.sessionDetails.smparticipants___undefinedElement;
		}

		//console.log('getParticipantContainer: #smparticipants_' + model.get('stepgroupLabel') + '_' + model.get('stepgroupLoop') + '_' + model.get('stepId'));

		container = $('#smparticipants_' + model.get('stepgroupLabel') + '_' + model.get('stepgroupLoop') + '_' + model.get('stepId'));

		//console.log(container);
		if (container.length === 0)
		{
			//console.log('container not found');
			if (module.addStepgroupLoopContainer(model.get('stepgroupLabel'), model.get('stepgroupLoop')))
			{
				container = $('#smparticipants_' + model.get('stepgroupLabel') + '_' + model.get('stepgroupLoop') + '_' + model.get('stepId'));
			}
			else
			{
				container = module.sessionDetails.smparticipants___undefinedElement;
				//console.log('container creation failed');
			}
		}

		return container;
	};

	module.participantPlace = function(model)
	{
		var participantContainer, participant;

		participant = $('#p' + model.get('id'));
		if (!participant)
		{
			//console.log('participant not found when placing');
			return;
		}

		participantContainer = module.getParticipantContainer(model);
		participant.appendTo(participantContainer);

		// update all stepgroup visibilities
		module.updateStepgroupLoopContainerVisibility();
	};

	module.participantAdd = function(model, collection, options)
	{
		//console.log('participant added');
		var participant, participantContainer, type;

		participant = $('<div/>');
		participant.attr('id', 'p' + model.get('id'));
		participant.attr('title', model.get('label'));
		participant.addClass('participant');
		type = module.models.types.findWhere({'label': model.get('typeLabel')});
		if (type != undefined && type.has('icon') && type.get('icon') !== '')
		{
			participant.html('<img src="/_media/Icons/' + type.get('icon') + '" /> ' + _.escape(model.get('label')));
		}
		else
		{
			participant.text(model.get('label'));
		}


		participantContainer = module.getParticipantContainer(model);
		participant.appendTo(participantContainer);

		// update all stepgroup visibilities
		module.updateStepgroupLoopContainerVisibility();
	};

	module.participantChange = function(model, collection, options)
	{
		//console.log('participant changed');
	};

	module.sessionParticipantsChange = function(model, collection, options)
	{
		var furthestContact;

		module.sessionDetails.sessionParticipantsBackwardestStepElement.text(model.get('backwardestStep'));
		module.sessionDetails.sessionParticipantsFurthestStepElement.text(model.get('furthestStep'));

		furthestContact = model.get('furthestContact');
		if (furthestContact > 3600)
		{
			furthestContact = '> 1 h';
		}
		else if (furthestContact > 60)
		{
			furthestContact = Math.round(furthestContact / 60) + ' min';
		}
		else
		{
			furthestContact = furthestContact + ' sec';
		}
		module.sessionDetails.sessionParticipantsFurthestContactElement.text(furthestContact);
	};

	module.participantCheckLastContact = function()
	{
		var time, element;
		time = new Date().getTime() / 1000;

		module.models.participants.each(function(model)
		{
			element = $('#p' + model.get('id'));

			if (element.length === 0)
			{
				return;
			}

			if (!model.has('lastContact'))
			{
				return;
			}

			if (time - model.get('lastContact') > 30)
			{
				if (!element.hasClass('missing-participant'))
				{
					element.addClass('missing-participant');
				}
			}
			else
			{
				if (element.hasClass('missing-participant'))
				{
					element.removeClass('missing-participant');
				}
			}
		});

		window.setTimeout(module.participantCheckLastContact, 1500);
	};

	module.participantChangeProcess = function(model, value, options)
	{
		module.participantPlace(model);
	};

	module.participantRemove = function(model, collection, options)
	{
		$('#p' + model.get('id')).remove();
		module.updateStepgroupLoopContainerVisibility();
	};

	module.logAdd = function(model, collection, context)
	{
		var logElement;
		while (module.models.logs.length > 8)
		{
			module.models.logs.shift();
		}

		logElement = $('<li />', {id: 'logEntry' + model.get('id')});
		logElement.addClass(model.get('type'));
		logElement.html(model.get('content'));
		logElement.appendTo(module.sessionDetails.recentLogMessagesContainerElement);

		if (model.id > module.sessionDetails.lastLogId)
		{
			module.sessionDetails.lastLogId = model.get('id');
		}
	};

	module.logRemove = function(model, collection, context)
	{
		var logElement;

		logElement = $('#logEntry' + model.get('id'));
		if (logElement)
		{
			logElement.remove();
		}
	};

	module.sessionSetParticipantMgmt = function(participantMgmt)
	{
		bootbox.confirm(
			'Are you sure you want to use ' + participantMgmt + ' participant management?',
			function(result)
			{
				if (result)
				{
					module.sessionSet('participantMgmt', participantMgmt);
				}
			}
		);
	};

	module.sessionAddVariableContextChange = function()
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

	module.sessionVariableImport = function(textareaId)
	{
		var rawVariableInput = $('#' + textareaId)[0].value;

		if (rawVariableInput === '')
		{
			dojo.publish('messages', [{ message: 'Please input variables to import before submitting', type: "message"}]);
			return;
		}

		dojo.xhrPost({
			url: module.routes.variableImport + '/sessionId/' + module.sessionId,
			content: {sessionVariableImportContent: rawVariableInput},
			handleAs: 'json',
			preventCache: false,
			load: function(result)
			{
				if (dojo.exists('message', result))
				{
					dojo.publish('messages', [{ message: result.message, type: "message"}]);
				}
				else
				{
					dojo.publish('messages', [{ message: result.error, type: "error"}]);
				}
			},
			error: function(err) {
				dojo.publish('messages', [{ message: 'An error occurred!<br/>' + err.message, type: "error"}]);
			}
		});
	};

	module.sessionClearLog = function()
	{
		bootbox.confirm(
			'Do you really want to clear the session logs?',
			function(result)
			{
				if (result)
				{
					dojo.xhrPost({
						url: module.routes.logClear + '/sessionId/' + module.sessionId,
						handleAs: 'json',
						preventCache: false,
						load: function(result)
						{
							dojo.publish('messages', [{ message: result.message, type: "message"}]);
							dijit.byId('sessionLogTab').refresh();
						},
						error: function(err) {
							dojo.publish('messages', [{ message: 'An error occurred!<br/>' + err.message, type: "error"}]);
						}
					});
				}
			}
		);
	};

	module.sessionTabSubmit = function(targetUrl, formId, tabId)
	{
		var tab, tabHref, formData;

		formData = $('#' + formId).serializeArray();

		tab = dijit.byId(tabId);
		tabHref = tab.href;
		tab.set('content', tab.loadingMessage);
		tab._set('href', tabHref);
		tab._isDownloaded = true;

		$.ajax(
			{
				url: targetUrl,
				type: 'POST',
				data: formData,
				cache: false,
				success: function(data, textStatus, jqXHR)
				{
					tabHref = tab.href;
					tab.set('content', data);
					tab._set('href', tabHref);
					tab._isDownloaded = true;
				},
				error: function()
				{
					tab.refresh();
					dojo.publish('messages', [{ message: 'An error occurred!', type: "error"}]);
				}
			}
		);
	};

	module.sessionLogLoadPage = function(url, page)
	{
		var filterTypesParams = '';
		if (dojo.byId('filterTypes-error').checked)
		{
			if (filterTypesParams !== '')
			{
				filterTypesParams = filterTypesParams + '&';
			}
			filterTypesParams = filterTypesParams + 'filterTypes[]=error';
		}
		if (dojo.byId('filterTypes-warning').checked)
		{
			if (filterTypesParams !== '')
			{
				filterTypesParams = filterTypesParams + '&';
			}
			filterTypesParams = filterTypesParams + '&filterTypes[]=warning';
		}
		if (dojo.byId('filterTypes-notice').checked)
		{
			if (filterTypesParams !== '')
			{
				filterTypesParams = filterTypesParams + '&';
			}
			filterTypesParams = filterTypesParams + '&filterTypes[]=notice';
		}
		if (dojo.byId('filterTypes-debug').checked)
		{
			if (filterTypesParams !== '')
			{
				filterTypesParams = filterTypesParams + '&';
			}
			filterTypesParams = filterTypesParams + '&filterTypes[]=debug';
		}
		if (dojo.byId('filterTypes-event').checked)
		{
			if (filterTypesParams !== '')
			{
				filterTypesParams = filterTypesParams + '&';
			}
			filterTypesParams = filterTypesParams + '&filterTypes[]=event';
		}

		if (filterTypesParams !== '')
		{
			filterTypesParams = '?' + filterTypesParams;
		}

		dijit.byId('sessionLogTab').attr('href', url + '/page/' + page + filterTypesParams);
	};

	module.sessionSet = function(attributeName, attributeValue)
	{
		var requestParams = {};
		requestParams[attributeName] = attributeValue;

		$.ajax(
			{
				url: module.routes.sessionSet + '/sessionId/' + module.sessionId,
				data: requestParams,
				type: 'POST',
				cache: false,
				success: function(data, textStatus, jqXHR)
				{
					dojo.publish('messages', [{ message: data.message, type: "message"}]);
				},
				error: function()
				{
					dojo.publish('messages', [{ message: 'An error occurred!', type: "error"}]);
					/*error: function(err) {
						dojo.publish('messages', [{ message: 'An error occurred!<br/>' + err.message, type: "error"}]);
					}*/
				}
			}
		);
	};

	module.sessionDebugTreatmentcacheclear = function()
	{
		$.ajax(
			{
				url: module.routes.debugTreatmentcacheclear + '/sessionId/' + module.sessionId,
				type: 'POST',
				cache: false,
				success: function(data, textStatus, jqXHR)
				{
					dojo.publish('messages', [{ message: data.message, type: "message"}]);
				},
				error: function()
				{
					dojo.publish('messages', [{ message: 'An error occurred!', type: "error"}]);
					/*error: function(err) {
						dojo.publish('messages', [{ message: 'An error occurred!<br/>' + err.message, type: "error"}]);
					}*/
				}
			}
		);
	};

	module.sessionDebugTreatmentcacheprefill = function()
	{
		$.ajax(
			{
				url: module.routes.debugTreatmentcacheprefill + '/sessionId/' + module.sessionId,
				type: 'POST',
				cache: false,
				success: function(data, textStatus, jqXHR)
				{
					dojo.publish('messages', [{ message: data.message, type: "message"}]);
				},
				error: function()
				{
					dojo.publish('messages', [{ message: 'An error occurred!', type: "error"}]);
					/*error: function(err) {
						dojo.publish('messages', [{ message: 'An error occurred!<br/>' + err.message, type: "error"}]);
					}*/
				}
			}
		);
	};

	module.sessionDebugDeletesync = function()
	{
		$.ajax(
			{
				url: module.routes.debugDeletesync + '/sessionId/' + module.sessionId,
				type: 'POST',
				cache: false,
				success: function(data, textStatus, jqXHR)
				{
					dojo.publish('messages', [{ message: data.message, type: "message"}]);
				},
				error: function()
				{
					dojo.publish('messages', [{ message: 'An error occurred!', type: "error"}]);
					/*error: function(err) {
						dojo.publish('messages', [{ message: 'An error occurred!<br/>' + err.message, type: "error"}]);
					}*/
				}
			}
		);
	};

	module.sessionParticipantEditAll = function(sessiontypeId)
	{
		$.ajax(
			{
				url: module.routes.participantEditall + '/sessionId/' + module.sessionId,
				type: 'POST',
				data: $('#ExpadminFormParticipantEditAll').serialize(),
				cache: false,
				success: function(data, textStatus, jqXHR)
				{
					dojo.publish('messages', [{ message: data.message, type: "message"}]);
				},
				error: function()
				{
					dojo.publish('messages', [{ message: 'An error occurred!', type: "error"}]);
					/*error: function(err) {
						dojo.publish('messages', [{ message: 'An error occurred!<br/>' + err.message, type: "error"}]);
					}*/
				}
			}
		);
	};

	module.sessionParticipantEditType = function(sessiontypeId)
	{
		$.ajax(
			{
				url: module.routes.participantEdittype + '/sessionId/' + module.sessionId,
				type: 'POST',
				data: $('#ExpadminFormParticipantEditType').serialize(),
				cache: false,
				success: function(data, textStatus, jqXHR)
				{
					dojo.publish('messages', [{ message: data.message, type: "message"}]);
				},
				error: function()
				{
					dojo.publish('messages', [{ message: 'An error occurred!', type: "error"}]);
					/*error: function(err) {
						dojo.publish('messages', [{ message: 'An error occurred!<br/>' + err.message, type: "error"}]);
					}*/
				}
			}
		);
	};

	module.sessionParticipantAdd = function()
	{
		$.ajax(
			{
				url: module.routes.participantAdd + '/sessionId/' + module.sessionId,
				type: 'POST',
				data: $('#ExpadminFormParticipantAdd').serialize(),
				cache: false,
				success: function(data, textStatus, jqXHR)
				{
					dojo.publish('messages', [{ message: data.message, type: "message"}]);
				},
				error: function()
				{
					dojo.publish('messages', [{ message: 'An error occurred!', type: "error"}]);
					/*error: function(err) {
						dojo.publish('messages', [{ message: 'An error occurred!<br/>' + err.message, type: "error"}]);
					}*/
				}
			}
		);
	};

	module.sessionGroupAdd = function()
	{
		$.ajax(
			{
				url: module.routes.groupAdd + '/sessionId/' + module.sessionId,
				type: 'POST',
				data: $('#ExpadminFormGroupAdd').serialize(),
				cache: false,
				success: function(data, textStatus, jqXHR)
				{
					dojo.publish('messages', [{ message: data.message, type: "message"}]);
				},
				error: function()
				{
					dojo.publish('messages', [{ message: 'An error occurred!', type: "error"}]);
					/*error: function(err) {
						dojo.publish('messages', [{ message: 'An error occurred!<br/>' + err.message, type: "error"}]);
					}*/
				}
			}
		);
	};

	module.pollStepTimerDetails = function(stepId, stepgroupLoop)
	{
		var requestData = {
			stepId: stepId,
			stepgroupLoop: stepgroupLoop,
			details: '1',
		};

		$.ajax(
			{
				url: module.routes.optionsTimer + '/sessionId/' + module.sessionId,
				type: 'POST',
				data: requestData,
				cache: false,
				context: this,
				success: function (data)
					{
						$('#optionsTimerDetails').html(data);
						window.setTimeout(function ()
							{
								module.pollStepTimerDetails(stepId, stepgroupLoop);
							}, 5000
						);

					},
				error: function()
				{
					dojo.publish('messages', [{ message: 'An error occurred!', type: "error"}]);
				}
			}
		);
	};

	/*module.updateStepTimerDetails = function(id, timerStartTime, timerDuration, serverTime)
	{
		var timerStateElement = $('#' + id);

		if (timerStartTime == '')
		{
			timerStateElement.html('not running');
		}

		else if (timerStartTime > serverTime)
		{
			timerStateElement.html((timerStartTime - serverTime) + ' Seconds countdown');
		}

		else if (timerStartTime <= serverTime && timerStartTime + timerDuration >= serverTime)
		{
			timerStateElement.html((timerStartTime + timerDuration - serverTime) + ' Seconds left');
		}

		else
		{
			timerStateElement.html('ended');
		}
	};*/

	module.stepTimerStart = function(stepId, stepgroupLoop, context, contextLabel)
	{
		var date = new Date();

		var requestData = {
			stepId: stepId,
			stepgroupLoop: stepgroupLoop,
			startTime: Math.floor(date.getTime() / 1000),
			context: context
		};

		if (context !== 'E')
		{
			requestData.contextLabel = contextLabel;
		}

		$.ajax(
			{
				url: module.routes.optionsTimerset + '/sessionId/' + module.sessionId,
				type: 'POST',
				data: requestData,
				cache: false,
				success: function(data, textStatus, jqXHR)
				{
					dojo.publish('messages', [{ message: data.message, type: "message"}]);
				},
				error: function()
				{
					dojo.publish('messages', [{ message: 'An error occurred!', type: "error"}]);
				}
			}
		);
	};

	module.openDebugWindow = function(codePlacement, code, number)
	{
		var fullUrl = module.openDebugProtocol + '://';
		if (codePlacement == 'subdomain')
		{
			fullUrl += code + '-' + number + '.' + module.openDebugServerHost;
		}
		else if (codePlacement == 'none')
		{
			fullUrl += module.openDebugServerHost;
		}
		else
		{
			fullUrl += code + '-' + number + '-' + module.openDebugServerHost;
		}
		fullUrl += module.openDebugUrl + '/participantCode/' + code;

		window.open(fullUrl, 'TestWindow_' + module.sessionId + '_' + code, '');
	};

	window.expadmin = module;

}) ( window, window.jQuery, window._, window.dojo, window.dijit, window.bootbox, window.Backbone, window.console, window.Math, window.Date);