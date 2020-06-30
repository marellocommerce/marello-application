define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const BaseComponent = require('oroui/js/app/components/base/component');
    const CheckConnectionView = require('../views/check-connection-view');
    const CheckConnectionModel = require('../models/check-connection-model');
    const WebsiteProvider = require('../provider/website-provider');
    const Utils = require('../utils/utils');
    const CheckConnectionComponent = BaseComponent.extend({
        /**
         * @property {jQuery}
         */
        $el: null,

        /**
         * @property {CheckConnectionModel}
         */
        model: null,

        /**
         * @inheritDoc
         */
        constructor: function CheckConnectionComponent() {
            CheckConnectionComponent.__super__.constructor.apply(this, arguments);
        },

        /**
         * @param {Object} options
         */
        initialize: function(options) {
            CheckConnectionComponent.__super__.initialize.apply(this, arguments);

            this.model = new CheckConnectionModel({});
            this.$el = $(options._sourceElement);

            let viewOptions = this.getPreparedViewOptions(options);
            this.view = new CheckConnectionView(viewOptions);

            WebsiteProvider.setSubProvider(this);
        },

        /**
         * @param {Object} options
         * @return {Object}
         */
        getPreparedViewOptions: function(options) {
            let $form = this.$el.closest(options.parentElementSelector);
            let viewOptions = _.extend({
                model: this.model,
                el: $form,
                $checkConnectionStatusEl: $form.find(options.checkConnectionStatusSelector),
                selectorForFieldsRequiredReCheckConnection: options.selectorForFieldsRequiredReCheckConnection,
                transportEntityId: options.id
            }, options.viewOptions || {});

            let invalidOptionKeys = Utils.getInvalidJqueryOptionKeys(viewOptions, [
                'el', '$checkConnectionStatusEl'
            ]);

            if (invalidOptionKeys.length) {
                // unable to initialize
                this.$el.remove();
                throw new TypeError(
                    'Missing required form element(s): ' + invalidOptionKeys.join(', ')
                );
            }

            if (_.isUndefined(options.selectorForFieldsRequiredReCheckConnection)) {
                this.$el.remove();
                throw new TypeError('Missing required option: selectorForFieldsRequiredReCheckConnection');
            }

            invalidOptionKeys = Utils.getInvalidJqueryOptionKeys(
                options.selectorForFieldsRequiredReCheckConnection,
                _.keys(options.selectorForFieldsRequiredReCheckConnection)
            );

            if (invalidOptionKeys.length) {
                // unable to initialize
                this.$el.remove();
                throw new TypeError(
                    'Can\'t find element by key(s) in selectorForFieldsRequiredReCheckConnection: ' +
                    invalidOptionKeys.join(', ')
                );
            }

            return viewOptions;
        },

        /**
         * @return {Promise}
         */
        getWebsiteDTOsPromise: function () {
            return this.view.getWebsiteDTOsPromise();
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            WebsiteProvider.cleanSubProvider();

            const properties = ['$el', 'model'];
            _.each(properties, _.bind(function (property) {
                delete this[property];
            }, this));

            CheckConnectionComponent.__super__.dispose.call(this);
        }
    });


    return CheckConnectionComponent;
});
