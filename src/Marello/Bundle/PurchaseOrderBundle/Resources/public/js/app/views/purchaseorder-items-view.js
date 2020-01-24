define(function(require) {
    'use strict';

    var PurchaseOrderItemsView,
        $ = require('jquery'),
        _ = require('underscore'),
        routing = require('routing'),
        mediator = require('oroui/js/mediator'),
        AbstractItemsView = require('marellolayout/js/app/views/abstract-items-view');

    /**
     * @export marellopurchaseorder/js/app/views/purchaseorder-items-view
     * @extends marellolayout.app.views.AbstractItemsView
     * @class marellopurchaseorder.app.views.PurchaseOrderItemsView
     */
    PurchaseOrderItemsView = AbstractItemsView.extend({
        /**
         * @property {Object}
         */
        options: {
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            mediator.on('po:row:total:changed', this.triggerTotalsUpdateEvent, this);
            PurchaseOrderItemsView.__super__.initialize.apply(this, arguments);
        },

        triggerTotalsUpdateEvent: function(data) {
            var total = 0;
            var lineItems = this.$el.find('.purchase-order-line-item');
            _.each(lineItems, function(lineItem) {
                var $lineItem = $(lineItem);
                var amount = $lineItem.find('input[name*="orderedAmount"]').val();
                var price = $lineItem.find('input[name*="[purchasePrice][value]"]').val();
                var rowTotal = parseFloat(amount) * parseFloat(price);
                if (!isNaN(rowTotal)) {
                    total = total + rowTotal;
                }
            });
            mediator.trigger('po:items:total:changed', {'value': total.toFixed(2), 'currency': data.currency, 'type': 'additional', 'label': 'Additional items Total'});
        },

    });

    return PurchaseOrderItemsView;
});
