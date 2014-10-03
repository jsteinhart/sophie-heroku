if(!dojo._hasResource['symbic.Heartbeat'])
{

	dojo._hasResource['symbic.Heartbeat']=true;
	dojo.provide('symbic.Heartbeat');

	dojo.require('dojox.timing');

	dojo.declare('symbic.Heartbeat',null,
	{
		timer: null,
		interval: 60000,
		failedInterval: 2000,
		tickCounter : 0,

		checkUrl: (appBase ? appBase : '/') + 'sfwsystem/heartbeat',
		heartbeatJson: false,
		failed: false,
		lastFailed: false,
		failedCounter: 0,
		requestTimeout: 1000,

		alerted: false,
		alertThreshold: 3,
		alertRepeat: false,

		constructor: function(args) {
	        dojo.safeMixin(this,args);
			this.timer = new dojox.timing.Timer(this.interval);
			this.timer.onTick = dojo.hitch(this, this.tick);
			this.heartbeat = this;
	    },

		start: function()
		{
			this.timer.start();
		},

		stop: function()
		{
			this.timer.stop();
		},

		tick: function()
		{
			this.tickCounter++;
			this.onPreCheck();
			this.check();
		},

		check: function()
		{
			var handleAs = 'text';
			if (this.heartbeatJson)
			{
				handleAs = 'json';
			}

			dojo.xhrPost({
			    url: this.checkUrl,
			    handleAs: handleAs,
			    preventCache: true,
			    failOk: true,
			    /*timeout: this.requestTimeout,*/
			    load: dojo.hitch(this, this.handleCheckResponse),
			    error: dojo.hitch(this, this.handleCheckError)
			});
		},

		handleCheckResponse: function(result)
		{
		/*	if (this.heartbeatJson && !dojo.exists('heartbeat', result))
			{
				this.procFailed();
			}
			else if (this.heartbeatJson && result.heartbeat != 'success')
			{
				this.procFailed();
			}
			else
			{
				this.procSucceded(result);
			}
			this.onPostCheck();*/
		},

		handleCheckError: function(result)
		{
		/*	this.procFailed();
			this.onPostCheck();*/
		},

		procSucceded: function(result)
		{
			if (this.lastFailed)
			{
				this.timer.setInterval(this.interval);
			}

			this.lastFailed = false;
			this.failedCounter = 0;

			this.onSuccess();

			if (this.heartbeatJson)
			{
				if (dojo.exists('sessionTimeout', result))
				{
					if (result.sessionTimeout < 30)
					{
						this.sessionTimeout();
					}
				}
			}

			if (this.alerted)
			{
				this.alerted = false;
				this.clearAlert();
			}
		},

		procFailed: function()
		{
			if (!this.lastFailed)
			{
				this.timer.setInterval(this.failedInterval);
			}

			this.lastFailed = true;
			this.failedCounter++;

			this.onFailed();

			if (this.failedCounter >= this.alertThreshold && (!this.alerted || this.alertRepeat))
			{
				this.alerted = true;
				this.alert();
			}
		},

		onPreCheck: function()
		{
		},

		onSuccess: function()
		{
		},

		onFailed: function()
		{
		},

		onPostCheck: function()
		{
		},

		alert: function()
		{
			dojo.publish('messages', [{"message":"Server connection is broken","type":"warn"}] );
		},

		clearAlert: function()
		{
			dojo.publish('messages', [{"message":"Server connection available again","type":"message"}] );
		},

		sessionTimeout: function()
		{
			dojo.publish("/heartbeat/sessionTimeout", [{message:'Please refresh your login'}]);
		}
	});
}