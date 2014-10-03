(function( window, $, dojo, bootbox, Math, undefined)
{
	var module		= {},
		baseUrl 	= '/';
	
	module.name			= 'sfwsysadmin';
	module.baseUrl		= baseUrl + module.name;

	module.models = {
	};

	module.routes		= {
		logFiletruncate:			module.baseUrl + '/log/filetruncate',

		installerRuninstall:		module.baseUrl + '/installer/runinstall',
		installerRuninstallsvn:		module.baseUrl + '/installer/runinstallsvn',

		userAdd:					module.baseUrl + '/user/add',
		userEdit:					module.baseUrl + '/user/edit',
		userSetactive:				module.baseUrl + '/user/setactive',
		userDelete:					module.baseUrl + '/user/delete',

		usergroupAdd:				module.baseUrl + '/usergroup/add',
		usergroupEdit:				module.baseUrl + '/usergroup/edit',
		usergroupDelete:			module.baseUrl + '/usergroup/delete'
	};
	
	module.evalXhrResult = function(result)
	{
		if (result.hasOwnProperty('error'))
		{
			dojo.publish('messages', [{ message: result.error, type: "error"}]);
			return false;
		}

		if (result.hasOwnProperty('message'))
		{
			dojo.publish('messages', [{ message: result.message, type: "message"}]);
			return true;
		}

		dojo.publish('messages', [{ message: 'Request succeeded', type: "message"}]);
		return true;
	};

	module.logFiletruncate = function(fileId, reload)
	{
		bootbox.confirm(
			'Do you really want to truncate this log file?',
			function(result)
			{
				if (result)
				{
					dojo.xhrPost({
						url: module.routes.logFiletruncate,
						content: {fileId:fileId},
						handleAs: 'json',
						preventCache: true,
						load: function(result)
						{
							if (module.evalXhrResult(result))
							{
								if (reload === 1)
								{
									window.location.reload();
								}
							}
						}
					});
				}
			}
		);
	};

	/*
	module.userListInit = function(result)
	{
		$('#userList').dataTable(
		{
			"aoColumnDefs":
			[
			  { "bSearchable": false, "aTargets": [ 5 ] },
			  { "bSortable": false, "aTargets": [ 5 ] }
			]
		});
		$('#userList tbody').on(
			"dblclick",
			"tr",
			function(e)
			{
				window.location.href = module.routes.userEdit + '/sessionId/' + e.currentTarget.getAttribute('data-pk');
				e.stopPropagation();
			}
		);
	};
	*/
	
	module.userSetactive = function(userId, active)
	{
		dojo.xhrPost({
			url: module.routes.userSetactive,
			content: {userId:userId, active:active},
			handleAs: 'json',
			preventCache: true,
			load: function(result)
			{
				if (module.evalXhrResult(result))
				{
					var activeCol = $('#userListActiveCol' + userId);
					if (active === 1)
					{
						activeCol.html('<a href="javascript:sfwsysadmin.userSetactive(' + userId + ', 0)"><img src="/_media/Icons/bullet_green.png" alt="Deactivate User" title="Deactivate User" border="0"></a>');
					}
					else
					{
						activeCol.html('<a href="javascript:sfwsysadmin.userSetactive(' + userId + ', 1)"><img src="/_media/Icons/bullet_red.png" alt="Activate User" title="Activate User"  border="0"></a>');
					}
				}
			}
		});
	};

	module.userDelete = function(userId)
	{
		bootbox.confirm(
			'Do you really want to delete this user?',
			function(result)
			{

				if (result)
				{
					dojo.xhrPost({
						url: module.routes.userDelete,
						content: {userId:userId},
						handleAs: 'json',
						preventCache: true,
						load: function(result)
						{
							$('#userListRow' + userId).remove();
							if (module.evalXhrResult(result))
							{
								$('#userListRow' + userId).remove();
							}
						}
					});
				}
			}
		);
	};

	module.usergroupDelete = function(usergroupId)
	{
		bootbox.confirm(
			'Do you really want to delete this usergroup?',
			function(result)
			{
				if (result)
				{
					dojo.xhrPost({
						url: module.routes.usergroupDelete,
						content: {usergroupId:usergroupId},
						handleAs: 'json',
						preventCache: true,
						load: function(result)
						{
							$('#usergroupListRow' + usergroupId).remove();
							if (module.evalXhrResult(result))
							{
								$('#usergroupListRow' + usergroupId).remove();
							}
						}
					});
				}
			}
		);
	};

	module.userGeneratePassword = function(passwordLength)
	{
		var validChars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz",
			password = '',
			i,
			rnum;
		
		for (i=0; i < passwordLength; i++)
		{
			rnum = Math.floor(Math.random() * validChars.length);
			password += validChars.substring(rnum,rnum+1);
		}
		
		return password;
	};

	module.userPlaceGeneratedPassword = function(field1, field2, showDiv)
	{
		var password;
		password = module.userGeneratePassword(8);
		$('#' + field1).value = password;
		$('#' + field2).value = password;
		$('#' + showDiv).html(": " + password);
	};

	module.installerRuninstall = function(form)
	{
		bootbox.confirm(
			'Do you really want to run the install script?',
			function(result)
			{
				if (result)
				{
					var installerLogContainer = $('#installerLog');
					if (installerLogContainer.hasClass('hidden'))
					{
						installerLogContainer.removeClass('hidden');
					}

					installerLogContainer.empty();
					installerLogContainer.append($('<div/>', {html: '<img src="/_media/ajax-loader.gif"> Loading ...'}).css('text-align', 'center'));

					$.ajax(
						{
							url: module.routes.installerRuninstall,
							type: 'POST',
							data: $('#SfwsysadminFormInstallerInstall').serialize(),
							cache: false,
							success: function(data, textStatus, jqXHR)
							{
								installerLogContainer.html(data);
							},
							error: function()
							{
								installerLogContainer.html('Installater failed');
							}
						}
					);
				}
			}
		);
	};

	module.installerRuninstallsvn = function()
	{
		bootbox.confirm(
			'Do you really want to run the install script?',
			function(result)
			{
				if (result)
				{
					var installerLogContainer = $('#installerLog');
					if (installerLogContainer.hasClass('hidden'))
					{
						installerLogContainer.removeClass('hidden');
					}
					
					installerLogContainer.empty();
					installerLogContainer.append($('<div/>', {html: '<img src="/_media/ajax-loader.gif"> Loading ...'}).css('text-align', 'center'));
					
					$.ajax(
						{
							url: module.routes.installerRuninstallsvn,
							type: 'POST',
							data: $('#SfwsysadminFormInstallerInstallsvn').serialize(),
							cache: false,
							success: function(data, textStatus, jqXHR)
							{
								installerLogContainer.html(data);
							},
							error: function()
							{
								installerLogContainer.html('Installater failed');
							}
						}
					);
				}
			}
		);
	};

	module.configMailChangeType = function()
	{
		if($('#transporttype')[0].value === 'file' || $('#transporttype')[0].value === 'sendmail')
		{
			$('#formTr-transporthost').css('display', 'none');
			$('#formTr-transportport').css('display', 'none');
			$('#formTr-transportssl').css('display', 'none');
			$('#formTr-transportauth').css('visibility', 'none');
			$('#formTr-transportusername').css('display', 'none');
			$('#formTr-transportpassword').css('display', 'none');

			if($('#transporttype')[0].value === 'file')
			{
				$('#formTr-informations td').html('No further configurations is needed.<br /> Your Emails will be stored in /var/log of your working Directory.');
			}
			else
			{
				$('#formTr-informations td').html('No further configurations is needed.');
			}
		}
		else
		{
			$('#formTr-transporthost').css('display', 'table-row');
			$('#formTr-transportport').css('display', 'table-row');
			$('#formTr-transportssl').css('display', 'table-row');
			$('#formTr-transportauth').css('display', 'table-row');
			$('#formTr-transportusername').css('display', 'table-row');
			$('#formTr-transportpassword').css('display', 'table-row');
			$('#formTr-informations td').html('');
		}
	};
	
	window.sfwsysadmin = module;
	
}) ( window, window.jQuery, window.dojo, window.bootbox, window.Math);


function sfwsysadminToggle(id)
{
	dojo.toggleClass(id, "logHidden");
	// remove selection:
	if(document.selection && document.selection.empty) {
		document.selection.empty();
	} else if(window.getSelection) {
		var sel = window.getSelection();
		sel.removeAllRanges();
	}
}