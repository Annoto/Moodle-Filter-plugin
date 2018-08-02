define(['jquery'], function($) {
 
    return {
        init: function(params) {
            this.params = params;
            if (!params) {
                this.logWarn('empty params');
                return;
            }

            require([params.bootstrapUrl], this.bootWidget.bind(this));
        },
        bootWidget: function() {
            var params = this.params;
            var that = this;
            var config = {
                clientId: params.clientId,
                position: params.position,
                features: {
                    tabs: params.featureTab,
                    cta: params.featureCTA,
                },
                ux :{
                    ssoAuthRequestHandle: function() {
                        window.location.replace(params.loginUrl)
                    },
                    logoutRequestHandle: function() {
                        window.location.replace(params.logoutUrl)
                    }
                },
                widgets: [
                    {
                        player: {
                            type: params.playerType,
                            element: params.playerId,
                            mediaDetails : function () {
                                return {
                                    title : params.mediaTitle,
                                    description: params.mediaDescription,
                                    group: {
                                        id: params.mediaGroupId,
                                        type: 'playlist',
                                        title: params.mediaGroupTitle,
                                        privateThread: params.privateThread,
                                    }
                                };
                            },
                        },
                        timeline: {
                            overlayVideo: false
                        },
                    }
                ],
                demoMode: params.demoMode,
                rtl: params.rtl,
                locale: params.locale,
            };

            if (window.Annoto) {
				window.Annoto.on('ready', function (api) {
				var jwt = params.userToken;
				if (api && jwt && jwt !== '') {
					api.auth(jwt).catch(function() {
						that.logError('Annoto SSO auth error');
					});
				}
			});
			if (params.playerType === 'videojs' && window.requirejs) {
				window.require(['media_videojs/video-lazy'], function(vjs) {
					config.widgets[0].player.params = {
						videojs: vjs
					};
					window.Annoto.boot(config);
				});
			} else {
				window.Annoto.boot(config);
			}
			} else {
				that.logWarn('Annoto not loaded');
			}
        },
        logWarn: function(msg, arg) {
            console && console.warn('AnnotoFilterPlugin: ' + msg, arg || '');
        },
        logError: function(msg, err) {
            console && console.error('AnnotoFilterPlugin: ' + msg, arg || '');
        }
    };
});