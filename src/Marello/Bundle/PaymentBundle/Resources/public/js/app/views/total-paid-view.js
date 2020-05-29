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
    const TotalPaidCurrenciesView = BaseView.extend({
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
            mediator.on('payment:form-changes:load', this.updateCurrencies, this);
            mediator.on('payment:form-changes:load:after', this.loadingEnd, this);
        },

        /**
         * @param {Object} data
         */
        updateCurrencies: function(data) {
            var currencies = data['currencies'] || null;
            if (!currencies) {
                this.loadingEnd();
                return;
            }

            this.render(currencies);
        },

        /**
         * Show loading view
         */
        loadingStart: function(e) {
            if (e.updateFields !== undefined && _.contains(e.updateFields, "currencies") !== true) {
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
         * @param {Object} currencies
         */
        render: function(currencies) {
            var selector = $("select[name='marello_payment_create[totalPaid][currency]']");
            selector.empty();
            $.each(currencies, function(text, value) {
                var option = $("<option></option>")
                    .attr("value", value)
                    .text(text);
                selector.append(option);
            });
            selector.trigger('change');
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off('payment:form-changes:trigger', this.loadingStart, this);
            mediator.off('payment:form-changes:load', this.updateCurrencies, this);
            mediator.off('payment:form-changes:load:after', this.loadingEnd, this);

            TotalPaidCurrenciesView.__super__.dispose.call(this);
        }
    });

    return TotalPaidCurrenciesView;
});
