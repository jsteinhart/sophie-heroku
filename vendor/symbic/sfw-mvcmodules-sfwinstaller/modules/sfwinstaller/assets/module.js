(function( window, $, undefined)
{
	var module,
		baseUrl = '/';
	
	module =
	{
	};

	module.routes = {
	};
	
	module.emailChangeVisibility = function()
	{
		if(document.getElementById('type').value === 'file' || document.getElementById('type').value === 'sendmail')
		{
			document.getElementById('formTr-host').style.display = 'none';
			document.getElementById('formTr-port').style.display = 'none';
			document.getElementById('formTr-ssl').style.display = 'none';
			document.getElementById('formTr-auth').style.visibility = 'hidden';
			document.getElementById('formTr-username').style.display = 'none';
			document.getElementById('formTr-password').style.display = 'none';
			document.getElementById('formTr-defaultEmail').style.display = 'none';

			if(document.getElementById('type').value === 'file')
			{
				document.getElementById('formTr-informations').innerHTML = '<th id="informations-label"></th><td>No further configurations needed <br> Your Emails will be stored in /var/log of your working Directory</td>';
			}
			else
			{
				document.getElementById('formTr-informations').innerHTML = '<th id="informations-label"></th><td>No further configurations needed</td>';	
			}
		}
		else
		{
			document.getElementById('formTr-host').style.display = 'table-row';
			document.getElementById('formTr-port').style.display = 'table-row';
			document.getElementById('formTr-ssl').style.display = 'table-row';
			document.getElementById('formTr-auth').style.visibility = 'visible';
			document.getElementById('formTr-username').style.display = 'table-row';
			document.getElementById('formTr-password').style.display = 'table-row';
			document.getElementById('formTr-defaultEmail').style.display = 'table-row';
			document.getElementById('formTr-informations').innerHTML = '<th id="informations-label"></th><td></td>';
		}
	};
	
	window.sfwinstaller = module;
	
}) ( window, window.jQuery);
		
/*window.onload = function(){
	document.getElementById('formTr-informations').innerHTML = '<th id="informations-label"></th><td></td>';
};*/