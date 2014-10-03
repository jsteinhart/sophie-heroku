(function( window, $, CodeMirror, console, rateLimitCall, undefined)
{
	var module		= {};

	module.instances = {};

	module.options = {};

	module.options.codemirrorDefault = {
		lineNumbers: true,
		styleActiveLine: true,
		lineWrapping: true,
		matchBrackets: true,
		viewportMargin: Infinity,
		mode: "text/x-php",
		indentUnit: 2,
		indentWithTabs: true,
		enterMode: "keep",
		tabMode: "shift",
		extraKeys: {
			"F11": function(cm)
			{
				cm.setOption("fullScreen", !cm.getOption("fullScreen"));
			},
			"Esc": function(cm)
			{
				if (cm.getOption("fullScreen"))
				{
					cm.setOption("fullScreen", false);
				}
			}
		},
		onChange: function (cm) { cm.save(); }
	};

	module.get = function (name)
	{
		return module.instances[name];
	};

	module.create = function (name, options)
	{
		var instance = {};

		instance.name = name;

		if (options)
		{
			instance.options = options;
		}
		else
		{
			instance.options = {};
		}

		if (!instance.options.hasOwnProperty('codemirror'))
		{
			instance.options.codemirror = module.options.codemirrorDefault;
		}

		instance.codemirrorInstance = null;

		instance.element = document.getElementById(instance.name);

		if (instance.element === null)
		{
			console.log('Initializing FormCodemirrorTextarea element failed');
			return;
		}

		instance.callbacks = {
			load:		$.Callbacks(),
			change:		$.Callbacks(),
			refresh:	$.Callbacks(),
			unload:		$.Callbacks()
		};

		instance.load = function()
		{
			if (instance.codemirrorInstance)
			{
				instance.unload();
			}

			$("#codemirrorInstanceToolbar_" + instance.name).removeClass("hidden");

			instance.codemirrorInstance = CodeMirror.fromTextArea(instance.element, instance.options.codemirror);
			instance.codemirrorInstance.on('change', instance.change);

			instance.callbacks.load.fireWith(instance);
		};

		instance.change = function()
		{
			instance.callbacks.change.fireWith(instance);
		};

		instance.refresh = function()
		{
			instance.codemirrorInstance.refresh();
			instance.callbacks.refresh.fireWith(instance);
		};

		instance.unload = function()
		{
			instance.codemirrorInstance.toTextArea();
			instance.codemirrorInstance = null;
			$("#codemirrorInstanceToolbar_" + instance.name).addClass("hidden");

			instance.callbacks.refresh.fireWith(instance);
		};

		instance.on = function(event, callback)
		{
			if (instance.callbacks[event])
			{
				instance.callbacks[event].add(callback);
			}
		};

		instance.off = function(event, callback)
		{
			if (instance.callbacks[event])
			{
				instance.callbacks[event].remove(callback);
			}
		};

		instance.replaceSelection = function(content)
		{
			instance.codemirrorInstance.replaceSelection(content);
		};

		instance.getValue = function()
		{
			return instance.codemirrorInstance.getValue();
		}

		instance.toggleFullscreen = function()
		{
			instance.codemirrorInstance.setOption("fullScreen", !instance.codemirrorInstance.getOption("fullScreen"));
			if (instance.codemirrorInstance.getOption("fullScreen"))
			{
				instance.codemirrorInstance.focus();
			}

			/*
			if (document.fullScreen)
			{
				document.cancelFullScreen();
			}
			else
			{
				instance.elementContsainer.requestFullScreen();
			}
			*/
		};

		instance.toggleLineWrapping = function()
		{
			instance.codemirrorInstance.setOption("lineWrapping", !instance.codemirrorInstance.getOption("lineWrapping"));
		};
		
		instance.load();

		module.instances[name] = instance;
	};

	window.SymbicFormCodemirrorTextarea = module;

}) (window, window.jQuery, window.CodeMirror, window.console, window.rateLimitCall);