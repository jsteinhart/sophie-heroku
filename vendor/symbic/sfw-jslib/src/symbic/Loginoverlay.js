if(!dojo._hasResource['symbic.Loginoverlay'])
{

	dojo._hasResource['symbic.Loginoverlay']=true;
	dojo.provide('symbic.Loginoverlay');

	dojo.require("dijit.Dialog");

	dojo.declare('symbic.Loginoverlay',null,
	{

		dialog: null,

		constructor: function(args)
		{
	        dojo.safeMixin(this,args);
		},

		show: function()
		{
			if (this.dialog == null)
			{
				this.dialog = new dijit.Dialog({
					title: "Refresh Login",
					content: '<table><tr><td>Username</td><td><input name="username"></td></tr><tr><td>Password</td><td><input type="password" value=""></td></tr><tr><td colspan="2"></td></tr></table>',
					style: "width: 300px"
				});
			}
		},
		subscribe: function(topic)
		{
			dojo.subscribe(topic, dojo.hitch(this, this.handler));
		},
		handler: function(data)
		{
			this.show();
		}
	});
}