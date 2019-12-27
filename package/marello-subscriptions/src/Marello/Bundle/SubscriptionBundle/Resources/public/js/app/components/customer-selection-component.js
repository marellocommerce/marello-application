define(function(require) {
    'use strict';

    const BaseComponent = require('oroui/js/app/components/base/component');
    const _ = require('underscore');
    const mediator = require('oroui/js/mediator');

    const CustomerSelectionComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            customerSelect: 'input[name*="customer"]'
        },

        /**
         * @property {Object}
         */
        $customerSelect: null,

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            this.$el = options._sourceElement;

            this.$customerSelect = this.$el.find(this.options.customerSelect);
            this.$el.on('change', this.options.customerSelect, _.bind(this.onCustomerChanged, this));
        },

        /**
         * Handle Customer change
         */
        onCustomerChanged: function() {
            mediator.trigger('customer:change', {customerId: this.$customerSelect.val()});
            mediator.trigger('subscription:form-changes:trigger', {updateFields: ['billingAddress', 'shippingAddress']});
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$el.off();

            CustomerSelectionComponent.__super__.dispose.call(this);
        }
    });

    return CustomerSelectionComponent;
});

