define(function(require) {
    'use strict';

    var ProductSuppliersView,
        $ = require('jquery'),
        _ = require('underscore'),
        routing = require('routing'),
        mediator = require('oroui/js/mediator'),
        AbstractItemsView = require('marellolayout/js/app/views/abstract-items-view');

    /**
     * @export marellosupplier/js/app/views/product-suppliers-view
     * @extends marellolayout.app.views.AbstractItemsView
     * @class marellosupplier.app.views.ProductSuppliersView
     */
    ProductSuppliersView = AbstractItemsView.extend({
        /**
         * @property {Object}
         */
        options: {
            suppliers: {},
            supplierDataRoute: 'marello_supplier_supplier_get_default_data',
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            ProductSuppliersView.__super__.initialize.apply(this, arguments);
        },

        /**
         * Doing something after loading child components
         */
        handleLayoutInit: function() {
            ProductSuppliersView.__super__.handleLayoutInit.apply(this, arguments);
            mediator.on('supplier:load:line-items-supplier', this.loadLineItemsSupplierData, this);
        },

        /**
         * @param {Array} data
         * @param {Function} callback
         */
        loadLineItemsSupplierData: function(data, callback) {
            $.ajax({
                url: routing.generate(this.options.supplierDataRoute, data),
                type: 'GET',
                success: function(response) {
                    callback(response);
                },
                error: function(response) {
                    callback();
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

            mediator.off('supplier:load:line-items-supplier', this.loadLineItemsSupplierData, this);
            ProductSuppliersView.__super__.dispose.call(this);
        }
    });

    return ProductSuppliersView;
});
