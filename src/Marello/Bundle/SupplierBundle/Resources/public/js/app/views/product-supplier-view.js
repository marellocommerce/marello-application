define(function(require) {
    'use strict';

    var ProductSupplierView,
        $ = require('jquery'),
        _ = require('underscore'),
        mediator = require('oroui/js/mediator'),
        AbstractItemView = require('marellolayout/js/app/views/abstract-item-view');

    /**
     * @export marellosupplier/js/app/views/product-supplier-view
     * @extends marellolayout.app.views.AbstractItemView
     * @class marellosupplier.app.views.ProductSupplierView
     */
    ProductSupplierView = AbstractItemView.extend({
        options: {},

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            ProductSupplierView.__super__.initialize.apply(this, arguments);
        },

        /**
         * Doing something after loading child components
         */
        handleLayoutInit: function() {
            ProductSupplierView.__super__.handleLayoutInit.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            ProductSupplierView.__super__.dispose.call(this);
        }
    });

    return ProductSupplierView;
});
