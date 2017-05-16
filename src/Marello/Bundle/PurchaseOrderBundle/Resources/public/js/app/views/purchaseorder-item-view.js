define(function(require) {
    'use strict';

    var PurchaseOrderItemView,
        $ = require('jquery'),
        _ = require('underscore'),
        mediator = require('oroui/js/mediator'),
        AbstractItemView = require('marellolayout/js/app/views/abstract-item-view');

    /**
     * @export marellopurchaseorder/js/app/views/purchaseorder-item-view
     * @extends marellolayout.app.views.AbstractItemView
     * @class marellopurchaseorder.app.views.PurchaseOrderItemView
     */
    PurchaseOrderItemView = AbstractItemView.extend({
        options: {
            priority: 0,
            canDropship: false
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            PurchaseOrderItemView.__super__.initialize.apply(this, arguments);
        }

    });

    return PurchaseOrderItemView;
});
