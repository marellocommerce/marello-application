define(function(require) {
    'use strict';

    const template =  require('tpl-loader!marelloorder/templates/order/totals.html');
    const $ = require('jquery');
    const _ = require('underscore');
    const mediator = require('oroui/js/mediator');
    const LoadingMaskView = require('oroui/js/app/views/loading-mask-view');
    const NumberFormatter = require('orolocale/js/formatter/number');
    const localeSettings = require('orolocale/js/locale-settings');
    const BaseView = require('oroui/js/app/views/base/view');

    /**
     * @export marelloorder/js/app/views/order-totals-view
     * @extends oroui.app.views.base.View
     * @class marelloorder.app.views.RefundTotalsView
     */
    const RefundTotalsView = BaseView.extend({
        /**
         * @property {Object}
         */
        options: {
            selectors: {
                amount: '[data-amount-container]',
                balance: '[data-balance-container]',
                total: '[data-total-container]'
            }
        },

        /**
         * @property {jQuery}
         */
        $amount: null,
        /**
         * @property {jQuery}
         */
        $balance: null,
        /**
         * @property {jQuery}
         */
        $total: null,

        /**
         * @property {Object}
         */
        template: template,

        /**
         * @property {LoadingMaskView}
         */
        loadingMaskView: null,

        /**
         * @property {Array}
         */
        items: [],

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);

            mediator.on('refund:form-changes:trigger', this.loadingStart, this);
            mediator.on('refund:form-changes:load', this.setTotals, this);
            mediator.on('refund:form-changes:load:after', this.loadingEnd, this);

            this.$amount = this.$el.find(this.options.selectors.amount);
            this.$balance = this.$el.find(this.options.selectors.balance);
            this.$total = this.$el.find(this.options.selectors.total);

            this.resolveTemplates();

            this.loadingMaskView = new LoadingMaskView({container: this.$el});

            this.setTotals(options);
        },

        /**
         * @param {Object} data
         */
        setTotals: function(data) {
            var totals = _.defaults(data, {totals: {amount: undefined, balance: {}, total: {}}}).totals;
            this.render(totals);
        },

        resolveTemplates: function() {
            if (typeof this.options.selectors.template === 'string') {
                this.template = _.template($(this.options.selectors.template).text());
            }
        },

        /**
         * Show loading view
         */
        loadingStart: function(e) {
            if (e.updateFields !== undefined && _.contains(e.updateFields, "totals") !== true) {
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
         * Render totals
         *
         * @param {Object} totals
         */
        render: function(totals) {
            if(totals !== undefined && totals.amount !== undefined) {
                this.$el.find('.grand_total div .attribute-item__description div').html(totals.total)
                this.$el.find('.refund_amount div .attribute-item__description div').html(totals.amount)
                this.$el.find('.refund_balance div .attribute-item__description div').html(totals.balance)
            }
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off('refund:form-changes:trigger', this.loadingStart, this);
            mediator.off('refund:form-changes:load', this.setTotals, this);
            mediator.off('refund:form-changes:load:after', this.loadingEnd, this);

            RefundTotalsView.__super__.dispose.call(this);
        }
    });

    return RefundTotalsView;
});
