define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const mediator = require('oroui/js/mediator');
    const LoadingMaskView = require('oroui/js/app/views/loading-mask-view');
    const BaseView = require('oroui/js/app/views/base/view');

    /**
     * @export marelloorder/js/app/views/order-totals-view
     * @extends oroui.app.views.base.View
     * @class marelloorder.app.views.OrderTotalsView
     */
    const PaymentMethodView = BaseView.extend({
        /**
         * @property {LoadingMaskView}
         */
        loadingMaskView: null,

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);

            this.loadingMaskView = new LoadingMaskView({container: this.$el});

            mediator.on('payment:form-changes:trigger', this.loadingStart, this);
            mediator.on('payment:form-changes:load', this.updatePaymentMethods, this);
            mediator.on('payment:form-changes:load:after', this.loadingEnd, this);
        },

        /**
         * @param {Object} data
         */
        updatePaymentMethods: function(data) {
            var paymentMethodsHtml = data['paymentMethods'] || null;
            if (!paymentMethodsHtml) {
                this.loadingEnd();
                return;
            }

            this.render(paymentMethodsHtml);
        },

        /**
         * Show loading view
         */
        loadingStart: function(e) {
            if (e.updateFields !== undefined && _.contains(e.updateFields, "paymentMethods") !== true) {
                return;
            }
            this.loadingMaskView.show();
        },

        /**
         * Hide loading view
         */
        loadingEnd: function() {
            this.loadingMaskView.hide();
        },

        /**
         * Render
         *
         * @param {String} paymentMethodHtml
         */
        render: function(paymentMethodHtml) {
            $("div[data-ftid='marello_payment_create_paymentMethod']").replaceWith(paymentMethodHtml);
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off('payment:form-changes:trigger', this.loadingStart, this);
            mediator.off('payment:form-changes:load', this.updatePaymentMethods, this);
            mediator.off('payment:form-changes:load:after', this.loadingEnd, this);

            PaymentMethodView.__super__.dispose.call(this);
        }
    });

    return PaymentMethodView;
});
