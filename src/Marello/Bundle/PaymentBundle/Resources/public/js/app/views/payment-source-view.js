define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const mediator = require('oroui/js/mediator');
    const BaseView = require('oroui/js/app/views/base/view');

    /**
     * @export marelloorder/js/app/views/order-discount-view
     * @extends oroui.app.views.base.View
     * @class marelloorder.app.views.OrderDiscountView
     */
    const OrderDiscountView = BaseView.extend({
        /**
         * @property {Object}
         */
        options: {},

        /**
         * @property {jQuery}
         */
        $field: null,

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});

            this.$field = this.$el.find(':input[data-ftid]');
            $(this.$field).on('change', function() {
                mediator.trigger('payment:form-changes:trigger', {updateFields: ['paymentMethods', 'currencies']});
            });
            $(this.$field).trigger('change');
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            OrderDiscountView.__super__.dispose.call(this);
        }
    });

    return OrderDiscountView;
});
