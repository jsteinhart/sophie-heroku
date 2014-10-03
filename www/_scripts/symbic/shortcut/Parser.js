if(!dojo._hasResource['symbic.shortcut.Parser'])
{

	dojo._hasResource['symbic.shortcut.Parser']=true;
	dojo.provide('symbic.shortcut.Parser');

	dojo.declare('symbic.shortcut.Parser', null,
	{
		connected: false,

		registeredShortcuts: null,

		constructor: function(args)
		{
	        dojo.safeMixin(this,args);

			dojo.connect(window, 'onkeypress', this, 'handleOnKeyPress');
			this.connected = true;
			this.registeredShortcuts = new Array();
	    },

		parse: function()
		{
			var shortcutElements = dojo.query('[symbicShortcut]');

			dojo.forEach(shortcutElements,
					function(n, i)
					{
						this.registeredShortcuts.push( n );
					},
					this
				);
		},

		handleOnKeyPress: function(evt)
		{

//evt.shiftKey
//evt.metaKey

			var shortcutSearch = '';

			if (evt.ctrlKey && evt.altKey)
			{
				shortcutSearch = 'CTRL+ALT+';
			}
			else if (evt.ctrlKey)
			{
				shortcutSearch = 'CTRL+';
			}
			else if (evt.altKey)
			{
				shortcutSearch = 'ALT+';
			}

			shortcutSearch = shortcutSearch + evt.charOrCode;

			dojo.forEach(this.registeredShortcuts,
				function(n, i)
				{
					if (n.getAttribute('symbicShortcut') == this.shortcutSearch)
					{
						if (n.tagName.toLowerCase() == 'a')
						{
							n.click();
						}
						else if(n.tagName.toLowerCase() == 'form')
						{
							n.submit();
						}
						else
						{
							console.log('Shortcut for element tag ' + n.tagName + ' can not be handled');
						}

						if (! n.getAttribute('symbicShortcutEventBubble') == 1)
						{
							dojo.stopEvent(this.event);
						}
					}
				},
				{event: evt, shortcutSearch : shortcutSearch}
			);
		}
	});
}