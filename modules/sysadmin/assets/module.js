var sysadmin = {
};

sysadmin.initSteptypeList = function()
{
};

sysadmin.trashErase = function(objectType, id)
{
	if (!confirm('Do you really want to erase this object permanently?'))
	{
		return;
	}

	dojo.xhrPost({
		url: '/sysadmin/trash/erase',
		content: {objectType:objectType, id:id},
		handleAs: 'json',
		preventCache: true,
		load: function(result) {
			if (sophieEvalXhrResult(result))
			{
				if (id == 'all')
				{
					dojo.destroy('trashDeleted' + objectType.charAt(0).toUpperCase() + objectType.slice(1) + 's');
				}
				else
				{
					dojo.destroy(objectType + 'ListRow' + id);
				}
			}
		}
	});
};

sysadmin.trashRestore = function(objectType, id)
{
	if (!confirm('Do you really want to restore this object?'))
	{
		return;
	}

	dojo.xhrPost({
		url: '/sysadmin/trash/restore',
		content: {objectType:objectType, id:id},
		handleAs: 'json',
		preventCache: true,
		load: function(result) {
			if (sophieEvalXhrResult(result))
			{
				if (id == 'all')
				{
					dojo.destroy('trashDeleted' + objectType.charAt(0).toUpperCase() + objectType.slice(1) + 's');
				}
				else
				{
					dojo.destroy(objectType + 'ListRow' + id);
				}

			}
		}
	});
};