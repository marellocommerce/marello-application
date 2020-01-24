define(function(require) {
    'use strict';

    var PurchaseOrderTotalsView;
    var template =  require('tpl!marellopurchaseorder/templates/purchaseorder/totals.html');
    var $ = require('jquery');
    var _ = require('underscore');
    var mediator = require('oroui/js/mediator');
    var BaseView = require('oroui/js/app/views/base/view');

    /**
     * @export marellopurchaseorder/js/app/views/purchaseorder-totals-view
     * @extends oroui.app.views.base.View
     * @class marellopurchaseorder.app.views.PurchaseOrderTotalsView
     */
    PurchaseOrderTotalsView = BaseView.extend({
        /**
         * @property {Object}
         */
        options: {
            selectors: {
                totalsContainer: '[data-totals-container]'
            }
        },

        /**
         * @property {array}
         */
        totals: {},
        
        currency: null,

        /**
         * @property {jQuery}
         */
        $totalsContainer: null,
        
        /**
         * @property {LoadingMaskView}
         */
        loadingMaskView: null,

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);

            mediator.on('po:items:total:changed', this.setTotals, this);

            this.$totalsContainer = this.$el.find(this.options.selectors.totalsContainer);

            this.setTotals(options);
        },

        /**
         * @param {Object} data
         */
        setTotals: function(data) {
            if (data.type !== undefined) {
                this.totals[data.type] = data;

                this.render(this.totals);
            }
        },

        /**
         * Render totals
         *
         * @param {array} totals
         */
        render: function(totals) {
            if (totals !== undefined) {
                var self = this;
                delete totals['grand'];
                var grandTotal = 0;
                $.each(totals, function(index, total){
                    grandTotal = grandTotal + parseFloat(total.value);
                    if (self.currency === null && total.currency !== null) {
                        self.currency = total.currency;
                    }
                });
                totals['grand'] = {'value': grandTotal.toFixed(2), 'currency': self.currency, 'type': 'grand', 'label': 'Grand Total'}
            }
            this.$totalsContainer.html('');
            $.each(totals, function(index, total){
                if (parseFloat(total.value) !== 0.00 && total.currency !== null) {
                    self.$totalsContainer.append(template(total));
                }
            });
        },

         /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off('po:items:total:changed', this.setTotals, this);

            PurchaseOrderTotalsView.__super__.dispose.call(this);
        }
    });

    return PurchaseOrderTotalsView;
});
