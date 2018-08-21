define(function(require) {
    'use strict';

    var PurchaseOrderItemView,
        $ = require('jquery'),
        _ = require('underscore'),
        routing = require('routing'),
        mediator = require('oroui/js/mediator'),
        AbstractItemView = require('marellolayout/js/app/views/abstract-item-view');

    /**
     * @export marellopurchaseorder/js/app/views/purchaseorder-item-view
     * @extends marellolayout.app.views.AbstractItemView
     * @class marellopurchaseorder.app.views.PurchaseOrderItemView
     */
    PurchaseOrderItemView = AbstractItemView.extend({
        /**
         * @property {Object}
         */
        options: {
            route: 'marello_purchase_order_supplier_product_price',
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            this.supplierEl = $('input[name="marello_purchase_order_create_step_two[supplier]"]');
            this.productEl = this.$el.find('td.purchase-order-line-item-product').find('input[name*="product"]');
            this.productEl.change(_.bind(this.updatePurchasePrice, this));
            this.amountEl = this.$el.find('td.purchase-order-line-item-ordered-amount').find('input');
            this.amountEl.change(_.bind(this.updateRowTotal, this));
            this.priceEl = this.$el.find('td.purchase-order-line-item-purchase-price').find('input[name*="value"]');
            var currencyLabel = this.priceEl.closest('.control-group').find('label').text();
            var start_pos = currencyLabel.indexOf('(') + 1;
            var end_pos = currencyLabel.indexOf(')',start_pos);
            this.currencySymbol = currencyLabel.substring(start_pos,end_pos)
            this.priceEl.change(_.bind(this.updateRowTotal, this));
            PurchaseOrderItemView.__super__.initialize.apply(this, arguments);
        },
        
        updatePurchasePrice: function() {
            if (this.supplierEl.val() !== undefined && this.productEl.val() !== undefined && this.supplierEl.val() !== '' && this.productEl.val() !== '') {
                var self = this;
                $.ajax({
                    url: routing.generate(this.options.route, {'productId': this.productEl.val(), 'supplierId': this.supplierEl.val()}),
                    type: 'GET',
                    success: function (json) {
                        if (json['purchasePrice'] !== null) {
                            self.priceEl.val(json['purchasePrice'].toFixed(2)).trigger('change');
                        } else {
                            self.priceEl.val('').trigger('change');
                        }
                    }
                });
            } else {
                this.priceEl.val('').trigger('change');
            }
        },

        updateRowTotal: function() {
            var rowTotal = parseFloat(this.amountEl.val()) * parseFloat(this.priceEl.val());
            if (!isNaN(rowTotal)) {
                this.$el.find('td.purchase-order-line-item-row-total').html(this.currencySymbol + rowTotal.toFixed(2));
            } else {
                this.$el.find('td.purchase-order-line-item-row-total').html('');
            }
            mediator.trigger('po:row:total:changed', {'currency': this.currencySymbol});
        },

        /**
         * remove single line item
         */
        removeRow: function() {
            this.$el.trigger('content:remove');
            this.remove();
            mediator.trigger('po:row:total:changed', {'currency': this.currencySymbol});
        }

    });

    return PurchaseOrderItemView;
});
