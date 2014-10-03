(function ()
{
	"use strict";

	if (!dojo._hasResource['symbic.Toaster'])
	{

		dojo._hasResource['symbic.Toaster'] = true;
		dojo.provide('symbic.Toaster');

		dojo.declare('symbic.Toaster', null, {
			toasterId: 'symbic_toaster',
			toasterClass: 'symbic_toaster',

			toastIdPrefix: 'symbic_toast',
			toastClass: 'symbic_toast',
			toasts: null,

			showDuration: 500,
			hideDuration: 1000,
			displayTime: 3000,

			toastIdCount: 0,

			subscribed: [],

			constructor: function (args)
			{
				// do not mix in subscribes directly
				var subscribe = [];
				if (dojo.exists('subscribe', args))
				{
					subscribe = args.subscribe;
					delete args.subscribe;
				}

				dojo.safeMixin(this, args);

				this.toasts = new Array();

				if (dojo.byId(this.toasterId) == null)
				{
					var toasterNode = dojo.create('div');
					dojo.attr(toasterNode, "id", this.toasterId);
					dojo.addClass(toasterNode, this.toasterClass);
					dojo.place(toasterNode, dojo.body());
				}

				dojo.forEach(subscribe, dojo.hitch(this, function (topic, i)
				{
					//console.log('toaster init subscribe:' + topic);
					this.subscribe(topic);
				}));

			},

			subscribe: function (topic)
			{
				//console.log('toaster subscribe:' + topic);
				if (dojo.indexOf(this.subscribed, topic) === -1)
				{
					this.subscribed.push(topic);
					dojo.subscribe(topic, dojo.hitch(this, this.messageHandler));
				}
			},

			messageHandler: function (data)
			{
				if (dojo.exists('message', data))
				{
					if (dojo.exists('type', data))
					{
						this.add(data.message, data.type);
					}
					else
					{
						this.add(data.message);
					}
				}
				else if (dojo.exists('error', data))
				{
					this.add(data.error, 'symbic_toaster_error');
				}
			},

			add: function (msg, tClass)
			{
				this.toastIdCount++;

				var toastId = this.toastIdPrefix + this.toastIdCount;
				var newNode = dojo.create('div');
				dojo.attr(newNode, "id", toastId);
				dojo.addClass(newNode, this.toastClass);
				dojo.place(newNode, dojo.body());
				dojo.attr(newNode, "innerHTML", msg);

				if (tClass != '')
				{
					if (tClass == 'error' || tClass == 'warning' || tClass == 'notice')
					{
						tClass = 'symbic_toaster_' + tClass;
					}
					dojo.addClass(newNode, tClass);
				}

				var size = dojo.marginBox(newNode);

				var toasterHeight = dojo.style(this.toasterId, 'height');
				dojo.style(this.toasterId, 'width', size.w + 'px');
				dojo.style(this.toasterId, 'height', (toasterHeight + size.h) + 'px');

				dojo.query('#' + this.toasterId + ' > *').forEach(dojo.hitch(this, function (existingNode)
				{
					var btm = parseInt(dojo.style(existingNode, 'bottom'), 10);
					var anim1 = dojo.animateProperty(
					{
						node: existingNode,
						duration: this.showDuration,
						properties: {
							bottom: {
								start: btm,
								end: (btm + size.h),
								units: 'px'
							}
						}
					});
					anim1.play();
				}));

				dojo.place(newNode, this.toasterId, 'last');
				dojo.style(newNode, 'right', 0);

				var anim2 = dojo.animateProperty(
				{
					node: newNode,
					duration: this.showDuration,
					properties: {
						bottom: {
							start: size.h * -1,
							end: 0,
							units: 'px'
						}
					}
				});

				var anim3 = dojo.animateProperty(
				{
					node: newNode,
					delay: this.displayTime,
					duration: this.hideDuration,
					properties: {
						right: {
							start: 0,
							end: size.w * -1,
							units: 'px'
						}
					}
				});

				anim2.onEnd = dojo.hitch(this, function ()
				{
					anim3.play();
				});
				anim3.onEnd = dojo.hitch(this, function ()
				{
					var toasterHeight = dojo.style(this.toasterId, 'height');
					dojo.style(this.toasterId, 'height', (toasterHeight - size.h) + 'px');
					dojo.destroy(newNode);
				});
				anim2.play();
			}
		});
	}

}());