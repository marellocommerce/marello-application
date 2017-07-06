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
            PurchaseOrderItemsView.__super__.initialize.apply(this, arguments);
        }

    });

    return PurchaseOrderItemsView;
});
