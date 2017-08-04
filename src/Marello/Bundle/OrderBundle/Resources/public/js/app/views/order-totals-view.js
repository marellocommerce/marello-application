define(function(require) {
    'use strict';

    var OrderTotalsView;
    var template =  require('tpl!marelloorder/templates/order/totals.html');
    var noDataTemplate =  require('tpl!marelloorder/templates/order/totals-no-data.html');
    var $ = require('jquery');
    var _ = require('underscore');
    var mediator = require('oroui/js/mediator');
    var LoadingMaskView = require('oroui/js/app/views/loading-mask-view');
    var NumberFormatter = require('orolocale/js/formatter/number');
    var localeSettings = require('orolocale/js/locale-settings');
    var BaseView = require('oroui/js/app/views/base/view');

    /**
     * @export marelloorder/js/app/views/order-totals-view
     * @extends oroui.app.views.base.View
     * @class marelloorder.app.views.OrderTotalsView
     */
    OrderTotalsView = BaseView.extend({
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
         * @property {Object}
         */
        noDataTemplate: noDataTemplate,

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

            mediator.on('order:form-changes:trigger', this.loadingStart, this);
            mediator.on('order:form-changes:load', this.setTotals, this);
            mediator.on('order:form-changes:load:after', this.loadingEnd, this);

            this.$totals = this.$el.find(this.options.selectors.totals);

            this.resolveTemplates();

            this.loadingMaskView = new LoadingMaskView({container: this.$el});

            this.setTotals(options);
        },

        /**
         * @param {Object} data
         */
        setTotals: function(data) {
            var totals = _.defaults(data, {totals: {total: {}, subtotals: {}}}).totals;

            this.render(totals);
        },

        resolveTemplates: function() {
            if (typeof this.options.selectors.template === 'string') {
                this.template = _.template($(this.options.selectors.template).text());
            }

            if (typeof this.options.selectors.noDataTemplate === 'string') {
                this.noDataTemplate = _.template($(this.options.selectors.noDataTemplate).text());
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

            _.each(totals.subtotals, _.bind(this.pushItem, this));

            this.pushItem(totals.total);

            var items = _.filter(this.items);
            /*if (_.isEmpty(items)) {
                items = this.noDataTemplate();
            }*/

            this.$totals.html(items.join(''));

            this.items = [];
        },

        /**
         * @param {Object} item
         */
        pushItem: function(item) {
            var localItem = _.defaults(
                item,
                {
                    amount: 0,
                    currency: localeSettings.defaults.currency,
                    visible: false,
                    template: null,
                    signedAmount: 0,
                    data: {}
                }
            );

            if (localItem.visible === false) {
                return;
            }

            item.formattedAmount = NumberFormatter.formatCurrency(item.signedAmount, item.currency);

            if (item.data && item.data.baseAmount && item.data.baseCurrency) {
                item.formattedBaseAmount = NumberFormatter.formatCurrency(
                    item.data.baseAmount,
                    item.data.baseCurrency
                );
            }

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

            mediator.off('order:form-changes:trigger', this.loadingStart, this);
            mediator.off('order:form-changes:load', this.loadFormChanges, this);

            OrderTotalsView.__super__.dispose.call(this);
        }
    });

    return OrderTotalsView;
});
