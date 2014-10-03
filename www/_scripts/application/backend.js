function identifyExcelData(id)
{
	var excelData = dojo.byId(id).value;
	if (excelData.search(/\t/) > -1)
	{
		return true;
	}
	return false;
}

function convertExcelDataToCSV(id)
{
	var excelData = dojo.byId(id).value;
	var excelRows = excelData.split("\n");
	var rowCount = excelRows.length;

	var i, cleanExcelRow, cols;
	var newData = '';
	for (i = 0 ; i < rowCount; i = i + 1)
	{
		cleanExcelRow = excelRows[i].trim();
		cols = cleanExcelRow.split("\t");

		if ( cols.length > 1 )
		{
			newData = newData + cols.join(';');
		}
		else
		{
			newData = newData + cleanExcelRow;
		}

		if (i < rowCount)
		{
			newData = newData + "\n";
		}
	}

	dojo.byId(id).value = newData;
}

function identifyAndConvertExcelData(id)
{
	if(identifyExcelData(id))
	{
		convertExcelDataToCSV(id);
	}
}

function sophieEvalXhrResult(result)
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

function sophieAdjustIFrameSize(frameId) {
	try {
		var frame = dojo.byId(frameId);
		var frameDoc = (frame.contentDocument) ? frame.contentDocument : document.frames[frameId].document;
		var frameSize = dojo.marginBox(frame.body);
		var frameDocSize = dojo.marginBox(frameDoc.body);

		dojo.style(frameId, 'height', Math.max(frameSize.h, frameDocSize.h * 1.5) + 'px');
		dojo.style(frameId, 'width', frameDocSize.w + 'px');
		console.log(frameSize);
	} catch (e) {}
}

function sophieParticipantTypeSelectUpdate(id, ids)
{
	var selectAll = true;
	var selectNone = true;
	var allChkbox = dojo.byId(ids.allId);

	if (id == ids.allId)
	{
		selectAll = allChkbox.checked;
		selectNone = false;
	}
	else
	{
		for (var i = 0; i < ids.options.length; i++)
		{
			var e = dojo.byId(ids.options[i]);
			if (!e.checked)
			{
				selectAll = false;
			}
			else
			{
				selectNone = false;
			}
		}
	}
	if (selectNone)
	{
		dojo.publish('messages', [{ message: 'You may not uncheck all Participant Types.', type: "message"}]);
		selectAll = true;
	}
	if (selectAll)
	{
		for (var i = 0; i < ids.options.length; i++)
		{
			var e = dojo.byId(ids.options[i]);
			e.checked = true;
		}
	}
	allChkbox.checked = selectAll;
}

function win(name, href)
{
	var w = window.open(href, name, 'height=450,width=900,location=no,menubar=no,resizable=yes,status=no,toolbar=no,dependent=yes,scrollbars=yes');
	w.focus();
}

var rateLimitCallTimeouts = new Array();
function rateLimitCall(callIdenifier, limitedCall, callDelay)
{
	if (rateLimitCallTimeouts[callIdenifier])
	{
		clearTimeout(rateLimitCallTimeouts[callIdenifier]);
		//console.log('cleared rate limit call: ' + callIdenifier);
	}

	rateLimitCallTimeouts[callIdenifier] = setTimeout(limitedCall, callDelay);
	//console.log('rate limit call: ' + callIdenifier);
}