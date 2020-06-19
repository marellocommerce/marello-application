define(function(require) {
    'use strict';

    var $ = require('jquery');
    var _ = require('underscore');
    var BaseComponent = require('oroui/js/app/components/base/component');
    var CheckConnectionView = require('../views/check-connection-view');
    var CheckConnectionModel = require('../models/check-connection-model');
    var CheckConnectionComponent;

    CheckConnectionComponent = BaseComponent.extend({
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
            var $form = $(options._sourceElement).closest(options.parentElementSelector);
            var viewOptions = _.extend({
                model: new CheckConnectionModel({}),
                el: $form,
                checkButtonEl: $(options._sourceElement),
                websiteListEl: $(options.websiteListSelector),
                salesGroupEl: $(options.salesGroupSelector),
                websiteToSalesChannelMappingEl: $(options.websiteToSalesChannelMappingSelector),
                checkConnectionStatusEl: $form.find(options.checkConnectionStatusSelector),
                transportEntityId: options.id
            }, options.viewOptions || {});

            var invalidOptionKeys = this.getInvalidJqueryOptionKeys(viewOptions, [
                'el', 'salesGroupEl', 'websiteToSalesChannelMappingEl', 'checkButtonEl', 'checkConnectionStatusEl'
            ]);

            if (invalidOptionKeys.length) {
                // unable to initialize
                $(options._sourceElement).remove();
                throw new TypeError('Missing required form element (s): ' + invalidOptionKeys.join(','));
            } else {
                this.view = new CheckConnectionView(viewOptions);
            }
        },

        /**
         * @param {Object} options
         * @param {array} elementKeys
         *
         * @return {array}
         */
        getInvalidJqueryOptionKeys: function (options, elementKeys) {
            var invalidKeys = _.filter(elementKeys, function (elementKey) {
                /**
                 * @var {jQuery} element
                 */
                var element = options[elementKey];
                return !_.isUndefined(element) && !element.length;
            });

            return invalidKeys.length;
        },
    });


    return CheckConnectionComponent;
});
