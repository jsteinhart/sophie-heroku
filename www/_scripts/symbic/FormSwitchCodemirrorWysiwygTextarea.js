(function( window, $, CodeMirror, tinymce, CKEDITOR, console, rateLimitCall, undefined)
{
	var module		= {};

	module.instances = {};

	module.options = {
		fullscreen: false,
		lineWrapping: true
	};

	module.options.codemirrorDefault = {
		lineNumbers: true,
		styleActiveLine: true,
		lineWrapping: module.options.lineWrapping,
		matchBrackets: true,
		viewportMargin: Infinity,
		mode: "application/x-httpd-php",
		indentUnit: 2,
		indentWithTabs: true,
		enterMode: "keep",
		tabMode: "shift",
		extraKeys: {
			"F11": function(cm)
			{
				cm.setOption("fullScreen", !cm.getOption("fullScreen"));
				if (cm.getOption("fullScreen"))
				{
					cm.focus();
				}
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

	module.options.tinymceDefault =
	{
		forced_root_blocks: false,
		menubar: "edit format insert table view tools",
		protect: [
			/<\?php.*?\?>/g // Protect php code
		],
		plugins: "autoresize,autolink,advlist,lists,table,visualblocks,visualchars,image,fullscreen,hr",
		tools: "inserttable"
	};

	module.options.ckeditorDefault =
	{
		toolbar: 'Basic',
		autoGrow_onStartup: true,
		basicEntities: false,
		resize_enabled: false,
		protectedSource: [
			/<\?[\s\S]*?\?>/g // Protect php code
		],
	};

	module.get = function (name)
	{
		return module.instances[name];
	};

	module.create = function (name, options)
	{
		var instance = {};

		instance.name = name;
		instance.element = document.getElementById(instance.name);

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
			instance.options.codemirror = $.extend({}, module.options.codemirrorDefault);
		}

		if (!instance.options.hasOwnProperty('tinymce'))
		{
			instance.options.tinymce = $.extend({}, module.options.tinymceDefault);
			instance.options.tinymce.selector = '#' + instance.name;
		}

		if (!instance.options.hasOwnProperty('ckeditor'))
		{
			instance.options.ckeditor = $.extend({}, module.options.ckeditorDefault);
		}

		instance.type = null;
		instance.codemirrorInstance = null;
		instance.tinymceInstance = null;
		instance.ckeditorInstance = null;

		if (instance.element === null)
		{
			console.log('Initializing FormSwitchCodemirrorWysiwygTextarea element failed');
			return;
		}

		instance.callbacks = {
			load:		$.Callbacks(),
			change:		$.Callbacks(),
			refresh:	$.Callbacks(),
			unload:		$.Callbacks()
		};

		instance.load_codemirror = function()
		{
			$("#codemirrorInstanceToolbar_" + instance.name).removeClass("hidden");
			instance.codemirrorInstance = CodeMirror.fromTextArea(instance.element, instance.options.codemirror);
			instance.codemirrorInstance.on('change', instance.change);
		};

		instance.unload_codemirror = function()
		{
			if (instance.codemirrorInstance !== null)
			{
				$("#codemirrorInstanceToolbar_" + instance.name).addClass("hidden");
				instance.codemirrorInstance.off('change', instance.change);
				instance.codemirrorInstance.toTextArea();
				instance.codemirrorInstance = null;
				instance.type = null;
			}
		};

		/*
		instance.load_tinymce = function()
		{
			instance.tinymceInstance = tinymce.get(instance.name);
			if (instance.tinymceInstance)
			{
				if (instance.tinymceInstance.isHidden())
				{
					instance.tinymceInstance.show();
				}
			}
			else
			{
				instance.tinymceInstance = tinymce.createEditor(instance.name, instance.options.tinymce);
				instance.tinymceInstance.render(true);
			}

			instance.tinymceInstance.on('change', function(tinemceInstance)
			{
				instance.changed();
			});

		};

		instance.unload_tinymce = function()
		{
			if (instance.tinymceInstance !== null)
			{
				instance.tinymceInstance.off('change', instance.changed);
				instance.tinymceInstance.hide();
				instance.type = null;
			}
		};
		*/

		instance.load_ckeditor = function()
		{
			instance.ckeditorInstance = CKEDITOR.replace(instance.name, instance.options.ckeditor);
			instance.ckeditorInstance.on('change', instance.change);
		};

		instance.unload_ckeditor = function()
		{
			if (instance.ckeditorInstance !== null)
			{
				instance.ckeditorInstance.removeListener( 'change', instance.change);
				instance.ckeditorInstance.destroy();
				instance.ckeditorInstance = null;
				instance.type = null;
			}
		};

		instance.getPreferredType = function()
		{
			var type = $.cookie("SymbicFormSwitchCodemirrorWysiwygTextarea_PreferredType__" + instance.name);
			if (type !== 'codemirror'
				//&& type !== 'tinymce'
				&& type !== 'ckeditor')
			{
				return 'codemirror';
			}
			return type;
		};

		instance.setPreferredType = function(type)
		{
			$.cookie("SymbicFormSwitchCodemirrorWysiwygTextarea_PreferredType__" + instance.name, type, {expires: 365, path: "/"});
		};

		instance.load = function(type)
		{
			if (instance.type === type)
			{
				return;
			}

			if (instance.type !== null)
			{
				instance.unload();
			}

			var loadFunc = instance['load_' + type];
			if (typeof loadFunc === 'function')
			{
				loadFunc();
				instance.type = type;
				instance.setPreferredType(type);
				instance.callbacks.load.fireWith(instance);
				
				// TODO: set options on edit ready
				// instance.setFullscreen(instance.options.fullscreen);
				// instance.setLineWrapping(instance.options.lineWrapping);
			}
			else
			{
				console.log('Could not switch to type ' + type);
			}
		};

		instance.change = function()
		{
			if (instance.type === 'codemirror')
			{
				instance.codemirrorInstance.save();
			}
			instance.callbacks.change.fireWith(instance);
		};

		instance.refresh = function()
		{
			if (instance.type === 'codemirror')
			{
				instance.codemirrorInstance.refresh();
			}
		};

		instance.unload = function()
		{
			var unloadFunc = instance['unload_' + instance.type];
			if (typeof  unloadFunc === 'function')
			{
				unloadFunc();
				instance.callbacks.unload.fireWith(instance);
			}
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
			if (instance.type === 'codemirror')
			{
				instance.codemirrorInstance.replaceSelection(content);
			}
			else if (instance.type === 'tinymce')
			{
				instance.tinymceInstance.execCommand('mceInsertContent', false, content);
			}
			else if (instance.type === 'ckeditor')
			{
				instance.ckeditorInstance.insertText(text);
			}
		};

		instance.getValue = function()
		{
			if (instance.type === 'codemirror')
			{
				return instance.codemirrorInstance.getValue();
			}
			else if (instance.type === 'tinymce')
			{
				return instance.tinymceInstance.getContent();
			}
			else if (instance.type === 'ckeditor')
			{
				return instance.ckeditorInstance.getData();
			}
		}

		instance.setFullscreen = function(active)
		{
			instance.options.fullscreen = active;

			if (instance.type === 'codemirror')
			{
				instance.codemirrorInstance.setOption("fullScreen", instance.options.fullscreen);
				if (instance.options.fullscreen)
				{
					instance.codemirrorInstance.focus();
				}
			}
			else if (instance.type === 'tinymce')
			{
				// TODO
			}
			else if (instance.type === 'ckeditor')
			{
				console.log(instance.ckeditorInstance.commands.maximize.state);
				if (instance.options.fullscreen)
				{
					instance.ckeditorInstance.execCommand('maximize');
				}
			}
			/*
			// TODO add prefixes
			if (document.fullScreen)
			{
				document.cancelFullScreen();
			}
			else
			{
				if (instance.elementContainer.requestFullscreen)
				{
					instance.elementContainer.requestFullscreen();
				}
				else if (instance.elementContainer.msRequestFullscreen)
				{
					instance.elementContainer.msRequestFullscreen();
				}
				else if (instance.elementContainer.mozRequestFullScreen)
				{
					instance.elementContainer.mozRequestFullScreen();
				}
				else if (instance.elementContainer.webkitRequestFullscreen)
				{
				  instance.elementContainer.webkitRequestFullscreen();
				}
			}
			*/
		};
		
		instance.toggleFullscreen = function()
		{
			instance.setFullscreen(!instance.options.fullscreen);
		};

		instance.setLineWrapping = function(active)
		{
			instance.options.lineWrapping = active;
			
			if (instance.type === 'codemirror')
			{
				instance.codemirrorInstance.setOption("lineWrapping", instance.options.lineWrapping);
			}
		};

		instance.toggleLineWrapping = function()
		{
			instance.setLineWrapping(!instance.options.lineWrapping);
		};

		instance.load(instance.getPreferredType());

		module.instances[name] = instance;
	};

	window.SymbicFormSwitchCodemirrorWysiwygTextarea = module;

}) (window, window.jQuery, window.CodeMirror, window.tinymce, window.CKEDITOR,window.console, window.rateLimitCall);