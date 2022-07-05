define(function(require) {
    'use strict';

    const template =  require('tpl-loader!marellorefund/templates/refund/totals.html');
    const $ = require('jquery');
    const _ = require('underscore');
    const mediator = require('oroui/js/mediator');
    const LoadingMaskView = require('oroui/js/app/views/loading-mask-view');
    const NumberFormatter = require('orolocale/js/formatter/number');
    const BaseView = require('oroui/js/app/views/base/view');

    /**
     * @export marellorefund/js/app/views/refunds-totals-view
     * @extends oroui.app.views.base.View
     * @class marellorefund.app.views.RefundTotalsView
     */
    const RefundTotalsView = BaseView.extend({
        /**
         * @property {Object}
         */
        options: {
            selectors: {
                totals: '[data-totals-container]'
            }
        },

        /**
         * @property {jQuery}
         */
        $totals: null,

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

            this.$totals = this.$el.find(this.options.selectors.totals);

            this.resolveTemplates();

            this.loadingMaskView = new LoadingMaskView({container: this.$el});

            this.setTotals(options);
        },

        /**
         * @param {Object} data
         */
        setTotals: function(data) {
            var totals = _.defaults(data, {totals: {subtotal: {}, tax_total: {}, grand_total: {}}}).totals;
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
            this.items = [];
            if (totals !== undefined &&
                totals.subtotal !== undefined &&
                totals.tax_total !== undefined &&
                totals.grand_total !== undefined
            ) {
                this.pushItem(totals.subtotal, this.options.data.subtotalLabel);
                this.pushItem(totals.tax_total, this.options.data.taxtotalLabel);
                this.pushItem(totals.grand_total, this.options.data.grandtotalLabel);
            }
            var items = _.filter(this.items);

            this.$totals.html(items.join(''));
            this.items = [];
        },

        /**
         * @param {Object} item
         * @param {Object} label
         * @param {Object} amountValue
         */
        pushItem: function(item, label) {
            var localItem = _.defaults(
                item,
                {
                    amount: 0,
                    currency: this.options.data.currency,
                    visible: true,
                    template: null,
                    label: label
                }
            );

            item.formattedAmount = NumberFormatter.formatCurrency(item.amount, item.currency);
            var renderedItem = null;

            if (localItem.template) {
                renderedItem = _.template(item.template)({item: item});
            } else {
                renderedItem = this.template({item: item});
            }

            this.items.push(renderedItem);
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
