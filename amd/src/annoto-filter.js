// This file is part of Annoto/moodle-filter-plugin - http://annoto.net/
// https://github.com/Annoto/moodle-filter-plugin
// Annoto/moodle-filter-plugin is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Annoto/moodle-filter-plugin is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Annoto/moodle-filter-plugin.  If not, see <http://www.gnu.org/licenses/>.

/**
 * javscript for component 'filter_annoto'.
 *
 * @package    filter_annoto
 * @copyright  Annoto Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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