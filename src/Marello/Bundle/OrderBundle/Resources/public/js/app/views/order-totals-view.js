define(function(require) {
    'use strict';

    var OrderTotalsView;
    var template =  require('tpl!marelloorder/templates/order/totals.html');
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
            if (totals !== undefined && totals.subtotals !== undefined && totals.total !== undefined) {
                _.each(totals.subtotals, _.bind(this.pushItem, this));

                this.pushItem(totals.total);
            }
                var items = _.filter(this.items);

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
                    template: null
                }
            );

            if (localItem.visible === false) {
                return;
            }

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

            mediator.off('order:form-changes:trigger', this.loadingStart, this);
            mediator.off('order:form-changes:load', this.setTotals, this);
            mediator.off('order:form-changes:load:after', this.loadingEnd, this);

            OrderTotalsView.__super__.dispose.call(this);
        }
    });

    return OrderTotalsView;
});
