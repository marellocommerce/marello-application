define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const routing = require('routing');
    const mediator = require('oroui/js/mediator');
    const messenger = require('oroui/js/messenger');
    const BaseView = require('oroui/js/app/views/base/view');
    const logger = require('oroui/js/tools/logger');
    const WebsiteDTO = require('../dto/website');
    const CheckConnectionView = BaseView.extend({
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
        $checkConnectionStatusEl: null,

        /**
         * @property {int}
         */
        transportEntityId: null,

        /**
         * @property {jqXHR}
         */
        jqXHR: null,

        /**
         * @property {jqXHR}
         */
        websiteJqXHR: null,

        /**
         * @inheritDoc
         */
        constructor: function CheckConnectionView() {
            CheckConnectionView.__super__.constructor.apply(this, arguments);
        },

        initialize: function(options) {
            _.extend(this, _.pick(
                options,
                [
                    '$checkConnectionStatusEl',
                    'transportEntityId'
                ]
            ));
            this.url = this.getResolvedUrl();
            this.attachAdditionalEvents(options);
        },

        attachAdditionalEvents: function(options) {
            let selectorForFieldsRequiredReCheckConnection = options.selectorForFieldsRequiredReCheckConnection || [];
            _.each(selectorForFieldsRequiredReCheckConnection, _.bind(function (selector) {
                this.$el.on('change', selector, _.bind(this.resetToDefaultCheckConnectionData, this));
            }, this));
        },

        resetToDefaultCheckConnectionData: function() {
            this.model.setConnectionRequiredToCheck();
            this.model.resetWebsiteDTOsField();
        },

        /**
         * @returns {string}
         */
        getResolvedUrl: function() {
            let params = this.getIntegrationAndTransportTypeParams() || {};
            params =  _.extend({
                transportId: !_.isNull(this.transportEntityId) ? this.transportEntityId : 0
            }, params);

            return routing.generate(this.route, params);
        },

        getIntegrationAndTransportTypeParams: function() {
            let params = {};
            let fields = this.$el.formToArray();
            let integrationType = _.first(
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
                let typeEl = this.$el.find('[name$="[type]"]').first();
                if (typeEl.length) {
                    params.integrationType = typeEl.val();
                }
            }

            let transportType = _.first(
                _.filter(fields, function(field) {
                    return field.name.indexOf('[transportType]') !== -1;
                })
            );

            if (_.isObject(transportType)) {
                params.transportType = transportType.value;
            }

            let requiredMissed = ['integrationType', 'transportType'].filter(function(option) {
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
            let data = _.filter(fields, function(field) {
                return field.name.indexOf('[transport]') !== -1;
            });

            return _.map(data, function(field) {
                field.name = field.name.replace(/.+\[(.+)\]$/, 'check[$1]');
                return field;
            });
        },

        /**
         * @return {Promise}
         */
        getWebsiteDTOsPromise: function() {
            let deferredObject = $.Deferred();
            let model = this.model;
            // Get cached data in model in case if previous request was successful
            if (false === model.isConnectionRequiredToCheck()) {
                deferredObject.resolve(model.getWebsiteDTOs());
                return deferredObject;
            }

            // Update data in model
            let self = this;
            let data = this.getDataForRequestFromFields(this.$el.formToArray());
            this.websiteJqXHR = $.ajax({
                type: 'POST',
                url: this.url,
                data: $.param(data),
                success: this.saveResultSuccessHandler.bind(this),
                error: this.errorHandler.bind(this),
                complete: function() {
                    delete self.websiteJqXHR;
                }
            });

            this.websiteJqXHR.always(function () {
                if (!model.hasWebsites()) {
                    messenger.notificationFlashMessage(
                        'error',
                        __('marello.magento2.connection.no_websites')
                    );
                }

                deferredObject.resolve(model.getWebsiteDTOs());
            });
            return deferredObject;
        },

        onCheckConnection: function() {
            this.$el.validate();
            if (this.$el.valid()) {
                this.processCheckConnection(false);
            }
        },

        processCheckConnection: function() {
            if (this.jqXHR) {
                logger.warn(
                    'Trying to check connection while another request on checking connection in progress!',
                    {
                        request: this.jqXHR
                    }
                );

                return false;
            }

            let self = this;
            let data = this.getDataForRequestFromFields(this.$el.formToArray());
            this.jqXHR = $.ajax({
                type: 'POST',
                url: this.url,
                data: $.param(data),
                beforeSend: this.beforeSend.bind(this),
                success: this.checkConnectionSuccessHandler.bind(this),
                error: this.errorHandler.bind(this),
                complete: function() {
                    mediator.execute('hideLoading');
                    // Clear request object
                    delete self.jqXHR;
                }
            });
        },

        beforeSend: function () {
            this.$checkConnectionStatusEl.find('.alert').remove();
            mediator.execute('showLoading');
        },

        errorHandler: function() {
            this.resetToDefaultCheckConnectionData();
        },

        /**
         * @param {{success: bool, message: string, websites: object}} response
         */
        checkConnectionSuccessHandler: function(response) {
            let type = 'error';
            if (response.success) {
                type = 'success';
            }

            messenger.notificationFlashMessage(
                type,
                response.message,
                {
                    container: this.$checkConnectionStatusEl,
                    delay: 0
                }
            );

            this.saveResultSuccessHandler(response);
        },

        /**
         * @param {{success: bool, message: string, websites: object}} response
         */
        saveResultSuccessHandler: function(response) {
            if (response.success) {
                this.model.setConnectionChecked();
                let websites = _.map(response.websites, function (name, id) {
                    return new WebsiteDTO(id, name);
                });

                this.model.setWebsiteDTOs(websites);
            } else {
                this.resetToDefaultCheckConnectionData();
            }
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            if (this.jqXHR) {
                this.jqXHR.abort();
            }

            if (this.websiteJqXHR) {
                this.websiteJqXHR.abort();
            }

            const properties = ['jqXHR', 'websiteJqXHR', '$checkConnectionStatusEl'];
            _.each(properties, _.bind(function (property) {
                delete this[property];
            }, this));

            CheckConnectionView.__super__.dispose.call(this);
        }
    });

    return CheckConnectionView;
});
