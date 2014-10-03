function validateTabbedForm(formId)
{
	var contentPaneNodes = dojo.query(".dijitContentPane", dojo.byId(formId));
	var contentPaneWidgets = contentPaneNodes.map(dijit.byNode);

	dojo.forEach(
		contentPaneWidgets,
		function(contentPaneWidget)
		{

			var formWidgets = contentPaneWidget.getChildren();

			var formWidgetsValid = dojo.every(
				formWidgets,
				function(formWidget)
				{
					if (formWidget.disabled || !formWidget.validate)
					{
						return true;
					}
					return formWidget.validate();
				}
			);

			if (!formWidgetsValid)
			{
				contentPaneWidget.set('iconClass', 'dijitIconError');
			}
			else
			{
				contentPaneWidget.set('iconClass', '');
			}
		}
	);
	dojo.publish('messages', [{ message: 'Form contains invalid fields. Please complete all required fields.', type: "message"}]);
	return false;
}

var formChangeObservers = new Array();
function initFormChangeObserver(formId)
{
	var form = dijit.byId(formId);
	formChangeObservers[formId] = false;

	var formWidgets = form.getChildren();
	dojo.forEach(formWidgets,
		function(formWidget, i)
		{
			dojo.connect(formWidget, 'onChange', form, formChangeHandler);
			dojo.connect(formWidget, 'onKeyUp', form,  formChangeHandler);
		}
	);
}

function formChangeHandler(e)
{
	alert(this);
	alert(dojo.byId(this.id));
	submit = dojo.query('submit', dojo.byId(this.id));
	alert(submit);
//	submitDijit = dijit.byNode(submit);
//	submitDijit.set('active', true);
}

function generatePassword() {
	var validChars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
	var password = '';
	for (var i=0; i < 8; i++) {
		var rnum = Math.floor(Math.random() * validChars.length);
		password += validChars.substring(rnum,rnum+1);
	}
	return password;
}

function placeGeneratedPassword(field1, field2, showDiv) {
	password = generatePassword();
	dojo.byId(field1).value = password;
	dojo.byId(field2).value = password;
	dojo.byId(showDiv).innerHTML = ": " + password;
}

function addOnResize(/* function */ f)
{
	var old = window.onresize;
	window.onresize = function()
	{
		if (typeof old === 'function')
		{
			old();
		}
		f();
	};
}

function resizeDijitHeight(id, padding, minHeight)
{
	if (padding == undefined)
	{
		padding = 0;
	}
	padding = parseInt(padding);
	
	if (minHeight == undefined)
	{
		minHeight = 500;
	}
	minHeight = parseInt(minHeight);
	var dijitPosition = dojo.position(id, true);
	var windowSize = dojo.window.getBox();
	var height = Math.max(minHeight, windowSize.h - dijitPosition.y - padding);
	dojo.style(id, 'height', height + 'px');
	dijit.byId(id).resize();
}

function symbicEvalXhrResult(result)
{
	if (dojo.exists('error', result))
	{
		dojo.publish('messages', [{ message: result.error, type: "error"}]);
		return false;
	}

	if (dojo.exists('message', result))
	{
		dojo.publish('messages', [{ message: result.message, type: "message"}]);
		return true;
	}

	dojo.publish('messages', [{ message: 'Request succeeded', type: "message"}]);
	return true;
}