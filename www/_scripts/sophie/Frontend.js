(function () {
   "use strict";

	if(!dojo._hasResource['sophie.Frontend'])
	{
		dojo._hasResource['sophie.Frontend']=true;
		dojo.provide('sophie.Frontend');

		dojo.declare('sophie.Frontend', null,
		{
			timeSyncStart: true,
			timeSyncInterval: 30000,

			stepSyncStart: true,
			stepSyncInterval: 1800,
			stepSyncTolerance: 500,

			timerUpdateStart: true,
			timerUpdateInterval: 100,

			nextStepDelay: 500,

			calculateLag: true,
			debug: false,
			
			_contextChecksum: null,
			_timerEnabled: false,
			_timerGracePeriodClient: 500,
			_timerWarning: '',

			_state: 'loading',
			_lastTimerContainer: null,
			_timerCountdownContainer: 'sophie_countdown_timer',
			_timerContainer: 'sophie_timer',
			_timerWarningContainer: 'sophie_timer_warning',

			// startupContent, mainContent
			_timerShowOnStartup: 'startupContent',
			// countdownContent, startupContent, mainContent
			_timerShowOnCountdown: 'countdownContent',

			// Jobs:
			_timeSyncActive: false,
			_timeSyncTimeout: null,
			_timeSyncLocalTime: null,

			_stepSyncActive: false,
			_stepSyncTimeout: null,
			_stepSyncLocalTime: null,
			_stepSyncLastResult: null,

			_timerUpdateActive: false,
			_timerUpdateInterval: null,

			// Job results:
			_timerStartTime: null,
			_timerDuration: 0,
			_timerCountdownEnabled: false,
			_timerCountdownDuration: 0,
			_timerProceedBeforeTimeout: true,
			_timerOnTimeout: 'continue',
			_timerDisplay: false,

			// _timeDiff = (server time + lag) - client time
			// <=> server time = client time + _timeDiff <=> client time = server time - _timeDiff
			_timeDiff: 0,

			constructor: function(contextChecksum, args)
			{
				this._contextChecksum = contextChecksum;
				dojo.safeMixin(this, args);
				this._init();
			},

			_init: function()
			{
				if (!this._timerEnabled)
				{
					if (this.debug)
					{
					  console.log('Init SoPHIE Frontend without timer update enabled');
					}
					this.setState('content');
				}
				this._initTimers();
			},

			setState: function(state)
			{
				window.sophieFrontend.state = state;
				if (state == this._state)
				{
					return false;
				}

				if (this._state == 'content')
				{
					var now = new Date().getTime();
					var localTimerStartTime = this._timerStartTime - this._timeDiff;
					if (Math.abs(localTimerStartTime - now) < this.stepSyncTolerance)
					{
						return false;
					}
				}

				if (this.debug)
				{
					console.log('setting state ' + state + ', last state ' + this._state);
				}

				switch (state)
				{
					case 'loading':
					case 'startup':
					case 'countdown':
					case 'content':
					case 'end':
						this._switchState(state);
						break;

					default:
						console.log('Unkown state: ' + state);
						break;
				}
			},


			_addHiddenClass: function (nodeId)
			{
				var n = dojo.byId(nodeId);
				if (n)
				{
					dojo.addClass(n, 'hidden');
				}
			},

			_removeHiddenClass: function (nodeId)
			{
				var n = dojo.byId(nodeId);
				if (n)
				{
					dojo.removeClass(n, 'hidden');
				}
			},

			_switchState: function(state)
			{
				var lastState = this._state;
				if (lastState == state)
				{
					if (this.debug)
					{
						console.log('stay in state ' + lastState);
					}
					return;
				}
				
				if (this.debug)
				{
					console.log('switch from state ' + lastState + ' to ' + state);
				}

				this._addHiddenClass('sophie_steploading');
				this._addHiddenClass('sophie_stepstartup');
				this._addHiddenClass('sophie_stepcontent');
				this._addHiddenClass('sophie_stepcountdown');
				this._addHiddenClass('sophie_stepend');
				this._addHiddenClass(this._timerCountdownContainer);

				dojo.publish('/sophie/state/end', [{'state':lastState}]);

				switch (state)
				{
					case 'loading':
						this._removeHiddenClass('sophie_step' + state);
						break;

					case 'startup':
						if (this._timerShowOnStartup == 'mainContent')
						{
							this._removeHiddenClass('sophie_stepcontent');
						}
						else
						{
							this._removeHiddenClass('sophie_step' + state);
						}
						break;

					case 'countdown':
						if (this._timerShowOnCountdown == 'mainContent')
						{
							this._removeHiddenClass('sophie_stepcontent');
						}
						else if (this._timerShowOnCountdown == 'startupContent')
						{
							this._removeHiddenClass('sophie_stepstartup');
						}
						else
						{
							var tc = dojo.byId(this._timerCountdownContainer);
							if (tc)
							{
								tc.innerHTML = '';
								dojo.removeClass(tc, 'hidden');
							}
							this._removeHiddenClass('sophie_step' + state);
						}
						break;

					case 'content':
						if (this._timerEnabled)
						{
							var tc = dojo.byId(this._timerContainer);
							if (tc)
							{
								tc.innerHTML = '';
								dojo.removeClass(tc, 'hidden');
							}
						}
						this._removeHiddenClass('sophie_step' + state);
						break;

					case 'end':
						this._removeHiddenClass('sophie_step' + state);
						break;
				}

				this._state = state;
				dojo.publish('/sophie/state/change', [{'oldState':lastState, 'newState':state}]);
				dojo.publish('/sophie/state/start', [{'state':state}]);

			},

			_initTimers: function()
			{
				if (this._timerEnabled)
				{
					if (this.timeSyncStart)
					{
						this.activateTimeSync();
					}
				}

				if (this.stepSyncStart)
				{
					this.activateStepSync();
				}

				if (this._timerEnabled)
				{
					if (this._timerEnabled && this.timerUpdateStart)
					{
						this.activateTimerUpdate();
					}
				}
			},

			activateTimeSync: function()
			{
				if (this._timeSyncActive !== true)
				{
					this._timeSyncActive = true;
					if (this.debug)
					{
						console.log('activated time sync');
					}
					this._timeSync();
				}
			},

			deactivateTimeSync: function()
			{
				if (this._timeSyncActive !== false)
				{
					this._timeSyncActive = false;
					if (this.debug)
					{
						console.log('deactivated time sync');
					}
					if (this._timeSyncTimeout)
					{
						window.clearTimeout(this._timeSyncTimeout);
					}
				}
			},

			_timeSync: function()
			{
				this._timeSyncLocalTime = new Date().getTime();
				dojo.xhrPost({
					url: '/expfront/ajax/servertime',
					handleAs: 'json',
					preventCache: true,
					load: dojo.hitch(this, function(result)
					{
						if (result.servertime)
						{
							var now = new Date().getTime();
							var lag = this.calculateLag ? Math.round((now - this._timeSyncLocalTime) / 2, 0) : 0;
							
							// TODO: avg multiple timediff requests
							this._timeDiff = result.servermillitime + lag - now;
							this._setTimeSyncTimeout();
						}
					}),
					error: dojo.hitch(function(err)
					{
						// dojo.publish('errorMessages', [{error:'server connection failed'}]);
						this._setTimeSyncTimeout();
					})
				});
			},

			_setTimeSyncTimeout: function ()
			{
				if (this._timeSyncActive !== false)
				{
					this._timeSyncTimeout = window.setTimeout(
						dojo.hitch(this, function() { this._timeSync(); }),
						this.timeSyncInterval);
				}
			},

			activateStepSync: function()
			{
				if (this._stepSyncActive !== true)
				{
					this._stepSyncActive = true;
					if (this.debug)
					{
						console.log('activated step sync');
					}
					this._stepSync();
				}
			},

			deactivateStepSync: function()
			{
				if (this._stepSyncActive !== false)
				{
					this._stepSyncActive = false;
					if (this.debug)
					{
						console.log('deactivated step sync');
					}
					if (this._stepSyncTimeout)
					{
						window.clearTimeout(this._stepSyncTimeout);
					}
				}
			},

			_stepSync: function()
			{
				dojo.xhrPost({
					url: '/expfront/ajax/stepsync',
					handleAs: 'json',
					content:
					{
						contextChecksum: this._contextChecksum,
						lastResult: dojo.toJson(this._stepSyncLastResult)
					},
					preventCache: true,
					load: dojo.hitch(this, function(result)
					{
						// goto next step
						if (dojo.exists('nextStep', result))
						{
							this._nextStep();
							return false;
						}

						if (result.state == 'nextStep')
						{
							this._nextStep();
							return false;
						}

						if (result.error)
						{
							//dojo.publish('errorMessages', [{error:'server connection failed'}]);
							//window.location.replace(window.location.href);
							this._setStepSyncTimeout();
							return false;
						}

						// result should contain the following:
						// //result.state
						// result.timerEnabled
						// result.timerStartTime
						// result.timerDuration
						// result.timerCountdownEnabled
						// result.timerCountdownDuration
						// result.timerProceedBeforeTimeout
						// result.timerOnTimeout
						// result.timerDisplay

						dojo.publish('/sophie/stepsync/result', [result]);

						// set state for the current step
						// do not set state from ajax, but from timerUpdate only
						//this.setState(result.state);

						// deactivate timer update
						if (dojo.exists('timerEnabled', result))
						{
							if (!result.timerEnabled && this._timerEnabled)
							{
								// switch to content state
								this.deactivateTimerUpdate();
								this.setState('content');
							}

							// activate timer update
							else if (result.timerEnabled && !this._timerEnabled)
							{
								this.activateTimerUpdate();
							}
						}

						if (dojo.exists('timerStartTime', result))
						{
							if (result.timerStartTime === null)
							{
								this._timerStartTime = null;
							}
							else
							{
								result.timerStartTime = parseInt(result.timerStartTime, 10);
								if (result.timerStartTime <= 0)
								{
									this._timerStartTime = null;
								}
								else
								{
									this._timerStartTime = result.timerStartTime;
								}
							}
						}

						if (dojo.exists('timerDuration', result))
						{
							if (result.timerDuration === null)
							{
								this._timerDuration = null;
							}
							else
							{
								this._timerDuration = parseInt(result.timerDuration, 10);
							}
						}

						if (dojo.exists('timerCountdownEnabled', result))
						{
							this._timerCountdownEnabled = result.timerCountdownEnabled;
						}

						if (dojo.exists('timerCountdownDuration', result))
						{
							if (result.timerCountdownDuration === null)
							{
								this._timerCountdownDuration = null;
							}
							else
							{
								this._timerCountdownDuration = parseInt(result.timerCountdownDuration, 10);
							}
						}

						if (dojo.exists('timerProceedBeforeTimeout', result))
						{
							this._timerProceedBeforeTimeout = result.timerProceedBeforeTimeout;
						}

						if (dojo.exists('timerOnTimeout', result))
						{
							this._timerOnTimeout = result.timerOnTimeout;
						}

						if (dojo.exists('timerDisplay', result))
						{
							this._timerDisplay = result.timerDisplay;
						}

						// save last result to inform server of diff to send
						this._stepSyncLastResult = result;

						this._setStepSyncTimeout();
					}),
					error: dojo.hitch(this, function(err)
					{
	//					dojo.publish('errorMessages', [{error:err}]);
	//					window.location.replace(window.location.href);
	//					return false;
						this._setStepSyncTimeout();

					})
				});
			},

			_setStepSyncTimeout: function ()
			{
				if (this._stepSyncActive !== false)
				{
					this._stepSyncTimeout = window.setTimeout(
					dojo.hitch(this, function() { this._stepSync(); }),
						this.stepSyncInterval);
				}
			},

			activateTimerUpdate: function()
			{
				if (this._timerUpdateActive !== true)
				{
					this._timerUpdateActive = true;
					if (this.debug)
					{
					  console.log('activated timer update');
					}
					this._timerUpdate();
				}
			},

			deactivateTimerUpdate: function()
			{
				if (this._timerUpdateActive !== false)
				{
					this._timerUpdateActive = false;
					if (this.debug)
					{
					  console.log('deactivated timer update');
					}
					if (this._timerUpdateTimeout)
					{
						window.clearTimeout(this._timerUpdateTimeout);
					}
				}
			},

			_timerUpdate: function()
			{
				var time = new Date().getTime();

				// no timer set yet
				if (this._timerStartTime === null || this._timerStartTime == 0)
				{
					if (this.debug)
					{
						console.log('startup: empty starttime');
					}
					this.setState('startup');
					this._setTimerUpdateTimeout();
					return false;
				}

				var localTimerStartTime = this._timerStartTime - this._timeDiff;

				// time before timer start without countdown
				var timeTillStart = localTimerStartTime - time;
				if (timeTillStart > 0)
				{
					if (this.debug)
					{
						console.log('time till start: ' + timeTillStart);
					}
					// time before timer start with activated countdown
					if (this._timerCountdownEnabled && timeTillStart <= this._timerCountdownDuration)
					{
						if (this.debug)
						{
							console.log('countdown: countdown enabled and timeTillStart <= ' + this._timerCountdownDuration);
						}
						this.setState('countdown');
						this._setCountdownClock(timeTillStart);
					}

					else
					{
						if (this.debug)
						{
							if (this._timerCountdownEnabled)
							{
								console.log('startup: countdown enabled but timeTillStart (' + timeTillStart + ') > countdown duration (' + this._timerCountdownDuration + ')');
							}
							else
							{
								console.log('startup: countdown disabled');
							}
						}
						this.setState('startup');
					}
				}

				// running timer within the content state
				else if (time <= localTimerStartTime + this._timerDuration + this._timerGracePeriodClient)
				{
					// console.log(time - (localTimerStartTime + this._timerDuration + this._timerGracePeriodClient));
					var remainingMicroseconds = this._timerDuration - (time - localTimerStartTime);
					// console.log(remainingSeconds);
					if (remainingMicroseconds < 0)
					{
						remainingMicroseconds = 0;
					}
					this.setState('content');
					this._setTimerClock(remainingMicroseconds);
				}

				// outrun timer without continue to next step
				else if (this._timerOnTimeout != 'continue')
				{
					this.setState('content');
					if (this._timerWarning != '')
					{
						var c = dojo.byId(this._timerWarningContainer);
						if (c)
						{
							this._addHiddenClass(this._timerContainer);
							c.innerHTML = this._timerWarning;
						}
					}
				}

				// outrun timer waiting to continue to next step
				else
				{
					this.setState('end');
				}

				this._setTimerUpdateTimeout();
			},

			_setTimerUpdateTimeout: function ()
			{
				if (this._timerUpdateActive !== false)
				{
					this._timerUpdateTimeout = window.setTimeout(
						dojo.hitch(this, function() { this._timerUpdate(); }),
						this.timerUpdateInterval);
				}
			},

			_formatClock: function(microseconds)
			{
				var seconds = Math.round(microseconds / 1000, 0)
				var hh = Math.floor(seconds / 3600);
				seconds -= hh * 3600;

				var mm = Math.floor(seconds / 60);
				seconds -= mm * 60;

				var ss = seconds;

				if (hh > 0 && mm < 10 ){
					mm = "0" + mm;
				}
				if (ss < 10 ) {
					ss = "0" + ss;
				}

				var clock = mm + ':' + ss;
				if (hh)
				{
					clock = hh + ':' + clock;
				}
				return clock;
			},

			_setCountdownClock: function(remainingMicroseconds)
			{
				var tc = dojo.byId(this._timerCountdownContainer);
				if (tc)
				{
					tc.innerHTML = this._formatClock(remainingMicroseconds);
				}
			},

			_setTimerClock: function(remainingMicroseconds)
			{
				var secondsRemaining = Math.round(remainingMicroseconds / 1000, 0);
				window.sophieFrontend.timer.secondsRemaining = secondsRemaining;
				window.sophieFrontend.timer.secondsRemainingFormatted = this._formatClock(remainingMicroseconds);
				var tc = dojo.byId(this._timerContainer);
				if (tc)
				{
					tc.innerHTML = (this._timerDisplay) ? window.sophieFrontend.timer.secondsRemainingFormatted : '';
				}
			},

			_nextStep: function ()
			{
				this.setState('end');
				this.deactivateTimeSync();
				this.deactivateStepSync();
				this.deactivateTimerUpdate();
				window.setTimeout(
					dojo.hitch(this, function()
					{
						console.log('sending nextstep form');
						dojo.byId('sophie_form_nextstep').submit();
					}),
					this.nextStepDelay);
			}

		});
	}

}());

// new definition style
// definition of window.sophieFrontend:
(function( window, $, dojo, console, undefined)
{
	var module = {};
	
	module.dojoFrontend = null;
	
	module.state = false;
	module.timer = {};
	module.timer.secondsRemaining = false;
	module.timer.secondsRemainingFormatted = '0:00';

	module.init = function(context, params)
	{
		dojo.require('sophie.Frontend');
		module.dojoFrontend = new sophie.Frontend(context, params);
	};

	module.getState = function ()
	{
		return module.state;
	};

	module.getSecondsRemaining = function ()
	{
		return module.timer.secondsRemaining;
	};

	module.getSecondsRemainingFormatted = function ()
	{
		return module.timer.secondsRemainingFormatted;
	};

	module.activateTimeSync = function ()
	{
		if (this._timeSyncActive !== true)
		{
			this._timeSyncActive = true;
			console.log('activated time sync');
			this._timeSync();
		}
	};

	module.deactivateTimeSync = function ()
	{
		if (this._timeSyncActive !== false)
		{
			this._timeSyncActive = false;
			console.log('deactivated time sync');
			if (this._timeSyncTimeout)
			{
				window.clearTimeout(this._timeSyncTimeout);
			}
		}
	};

	module.setState = function(state)
	{
		if (module.dojoFrontend.setState(state) === false)
		{
			return false;
		}
		module.state = state;
		return true;
	};
	
	module.activateStepSync = function()
	{
		module.dojoFrontend.activateStepSync();
	};

	module.deactivateStepSync = function()
	{
		module.dojoFrontend.deactivateStepSync();
	};
	
	module.activateTimerUpdate = function()
	{
		module.dojoFrontend.activateTimerUpdate();
	};

	module.deactivateTimerUpdate = function()
	{
		module.dojoFrontend.deactivateTimerUpdate();
	};
	
	window.sophieFrontend = module;
	
	// for compatibility reasons
	window.frontend = module.dojoFrontend;

}) (window, window.jQuery, window.dojo, window.console);
