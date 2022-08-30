define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const OrderAddressView = require('marelloorder/js/app/views/order-address-view');

    const OrderBillingAddressView = OrderAddressView.extend({
        options: {
            selectors: {
                useBillingAsShipping: '',
                shippingAddressBlock: ''
            }
        },

        $useBillingAsShipping: null,

        $shippingAddressBlock: null,

        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            OrderBillingAddressView.__super__.initialize.apply(this, arguments);

            this.$useBillingAsShipping = this.$el.find(this.options.selectors.useBillingAsShipping);
            this.$shippingAddressBlock = this.$el.closest('form')
                .find(this.options.selectors.shippingAddressBlock)
                .closest('.responsive-cell');

            this.onUseBillingAsShippingChange();
            this.$el.on(
                'change',
                this.options.selectors.useBillingAsShipping,
                _.bind(this.onUseBillingAsShippingChange, this)
            );
        },

        onUseBillingAsShippingChange: function() {
            if (this.$useBillingAsShipping.prop('checked')) {
                this.$shippingAddressBlock.addClass('hide');
            } else {
                this.$shippingAddressBlock.removeClass('hide');
            }
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$el.off(
                'change',
                _.bind(this.onUseBillingAsShippingChange, this)
            );

            OrderBillingAddressView.__super__.dispose.call(this);
        }
    });

    return OrderBillingAddressView;
});
