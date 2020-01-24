define(function(require) {
    'use strict';

    var OrderDiscountView;
    var $ = require('jquery');
    var _ = require('underscore');
    var mediator = require('oroui/js/mediator');
    var BaseView = require('oroui/js/app/views/base/view');

    /**
     * @export marelloorder/js/app/views/order-discount-view
     * @extends oroui.app.views.base.View
     * @class marelloorder.app.views.OrderDiscountView
     */
    OrderDiscountView = BaseView.extend({
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
                mediator.trigger('order:form-changes:trigger', {updateFields: ['totals']});
            });
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
