define(function(require) {
    'use strict';

    var CheckConnectionView;
    var $ = require('jquery');
    var _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var routing = require('routing');
    var mediator = require('oroui/js/mediator');
    var messenger = require('oroui/js/messenger');
    var BaseView = require('oroui/js/app/views/base/view');

    CheckConnectionView = BaseView.extend({
        /**
         * @property {string}
         */
        route: 'marello_magento2_integration_check',

        /**
         * @property {string}
         */
        url: '',

        /**
         * @property {Object}
         */
        events: {
            'click [data-role=check-connection-btn]': 'onCheckConnection'
        },

        /**
         * @property {jQuery}
         */
        checkButtonEl: null,

        /**
         * @property {jQuery}
         */
        websiteListEl: null,

        /**
         * @property {jQuery}
         */
        checkConnectionStatusEl: null,

        /**
         * @property {int}
         */
        transportEntityId: null,

        /**
         * @property {jqXHR}
         */
        jqXHR: null,

        /**
         * @inheritDoc
         */
        constructor: function CheckConnectionView() {
            CheckConnectionView.__super__.constructor.apply(this, arguments);
        },

        initialize: function(options) {
            _.extend(this, _.pick(
                options,
                ['checkButtonEl', 'websiteListEl', 'checkConnectionStatusEl', 'transportEntityId']
            ));
            this.url = this.getResolvedUrl();
        },

        /**
         * @returns {string}
         */
        getResolvedUrl: function() {
            var params = _.extend({
                id: this.id
            }, this.getIntegrationAndTransportTypeParams() || {});

            return routing.generate(this.route, params);
        },

        getIntegrationAndTransportTypeParams: function() {
            var params = {};
            var fields = this.$el.formToArray();
            var integrationType = _.first(
                _.filter(fields, function(field) {
                    return field.name.indexOf('[type]') !== -1;
                })
            );

            if (_.isObject(integrationType)) {
                params.integrationType = integrationType.value;
            } else {
                /**
                 * In case we on edit page and field type is disabled
                 * so we can't get it from element data array
                 */
                var typeEl = this.$el.find('[name$="[type]"]').first();
                if (typeEl.length) {
                    params.integrationType = typeEl.val();
                }
            }

            var transportType = _.first(
                _.filter(fields, function(field) {
                    return field.name.indexOf('[transportType]') !== -1;
                })
            );

            if (_.isObject(transportType)) {
                params.transportType = transportType.value;
            }

            var requiredMissed = ['integrationType', 'transportType'].filter(function(option) {
                return _.isUndefined(params[option]);
            });

            if (requiredMissed.length) {
                throw new TypeError('Missing required param(s): ' + requiredMissed.join(','));
            }

            return params;
        },

        /**
         * @param fields {Array}
         * @returns {Array}
         */
        getDataForRequestFromFields: function(fields) {
            var data = _.filter(fields, function(field) {
                return field.name.indexOf('[transport]') !== -1;
            });

            return _.map(data, function(field) {
                field.name = field.name.replace(/.+\[(.+)\]$/, 'check[$1]');
                return field;
            });
        },

        onCheckConnection: function() {
            this.$el.validate();

            if (this.$el.valid()) {
                this.checkConnection();
            }
        },

        checkConnection: function() {
            var data = this.getDataForRequestFromFields(this.$el.formToArray());
            this.jqXHR = $.ajax({
                type: 'POST',
                url: this.url,
                data: $.param(data),
                beforeSend: this.beforeSend.bind(this),
                success: this.successHandler.bind(this),
                complete: function() {
                    mediator.execute('hideLoading');
                }
            });
        },

        beforeSend: function () {
            this.checkConnectionStatusEl.find('.alert').remove();
            mediator.execute('showLoading');
        },

        /**
         * @param {{success: bool, message: string}} response
         */
        successHandler: function(response) {
            var type = 'error';
            if (response.success) {
                type = 'success';
            }

            messenger.notificationFlashMessage(
                type,
                response.message,
                {
                    container: this.checkConnectionStatusEl,
                    delay: 0
                }
            );
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            if (this.jqXHR) {
                this.jqXHR.abort();
            }

            CheckConnectionView.__super__.dispose.call(this);
        }
    });

    return CheckConnectionView;
});
