define(function(require) {
    'use strict';

    var EmbedMapView;
    var _ = require('underscore');
    var Backbone = require('backbone');
    var __ = require('orotranslation/js/translator');
    var localeSettings = require('orolocale/js/locale-settings');
    var LoadingMaskView = require('oroui/js/app/views/loading-mask-view');
    var BaseView = require('oroui/js/app/views/base/view');
    var messenger = require('oroui/js/messenger');
    var $ = Backbone.$;

    EmbedMapView = BaseView.extend({
        baseUrl: "https://www.google.com/maps/embed/v1/",

        apiKey: null,

        options: {
            mode: "place",
            mapOptions: {},
            iframeOptions: {
                allowfullscreen: true,
                width: 600,
                height: 450,
                frameborder: 0,
                style: 'border:none',
            }
        },

        loadingMask: null,

        /**
         * @inheritDoc
         */
        constructor: function EmbedMapView() {
            EmbedMapView.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            options.mapOptions = _.defaults(options.mapOptions || {}, this.options.mapOptions);
            options.iframeOptions = _.defaults(options.iframeOptions || {}, this.options.iframeOptions);

            this.options = _.defaults(
                options || {},
                this.options,
                _.pick(localeSettings.settings, ['apiKey'])
            );

            if (this.options.apiKey) {
                this.$mapContainer = $('<div class="map-visual"/>')
                    .appendTo(this.$el);

                this.loadingMask = new LoadingMaskView({container: this.$el});

                if (options.address) {
                    this.updateMap(options.address);
                } else {
                    this.mapLocationUnknown();
                }
            }
        },

        updateMap: function(address) {
            var map = $('<iframe/>');
            for (var attr in this.options.iframeOptions) {
                map.attr(attr, this.options.iframeOptions[attr]);
            }

            map.attr('src', this._buildUrl(address, this.mapOptions));

            this.$mapContainer.show();
            this.$mapContainer.html(map);
            this.loadingMask.hide();
        },

        _buildUrl: function(address, params) {
            var url = new URL(this.options.mode, this.baseUrl);
            var query = new URLSearchParams();
            query.set('q', this.options.address);
            query.set('key', this.options.apiKey);

            if (params) {
                for (var key in params) {
                    query.set(key, params[key]);
                }
            }

            url.search = query.toString();

            return url;
        },

        mapLocationUnknown: function() {
            this.$mapContainer.hide();
            this.addErrorMessage(__('map.unknown.location'));
            this.loadingMask.hide();
        },

        addErrorMessage: function(message, type) {
            this.removeErrorMessage();
            this.errorMessage = messenger.notificationFlashMessage(
                type || 'warning',
                message || __('map.unknown.unavailable'),
                {
                    container: this.$el,
                    hideCloseButton: true,
                    insertMethod: 'prependTo'
                }
            );
        },

        removeErrorMessage: function() {
            if (_.isNull(this.errorMessage)) {
                return;
            }
            messenger.clear(this.errorMessage.namespace, {
                container: this.$el
            });

            delete this.errorMessage;
        }
    });

    return EmbedMapView;
});
