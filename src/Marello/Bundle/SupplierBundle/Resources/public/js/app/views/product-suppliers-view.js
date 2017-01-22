define(function(require) {
    'use strict';

    var ProductSuppliersView,
        $ = require('jquery'),
        _ = require('underscore'),
        __ = require('orotranslation/js/translator'),
        routing = require('routing'),
        mediator = require('oroui/js/mediator'),
        layout = require('oroui/js/layout'),
        AbstractItemsView = require('marellolayout/js/app/views/abstract-items-view');

    /**
     * @export marelloproduct/js/app/views/product-suppliers-view
     * @extends marellolayout.app.views.AbstractItemsView
     * @class marelloproduct.app.views.ProductSuppliersView
     */
    ProductSuppliersView = AbstractItemsView.extend({
        /**
         * @property {Object}
         */
        options: {
            suppliers: {},
            supplierDataRoute: 'marello_supplier_supplier_data',
        },

        /**
         * @property {jQuery}
         */
        $form: null,

        /**
         * @property {Object}
         */
        $confirm: false,

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
            this.$supplier = this.$form.find(':input[data-ftid="' + this.$form.attr('name') + '_supplier"]');

            mediator.on('product:get:line-items-suppliers', this.getLineItemsSuppliers, this);
            mediator.on('product:load:line-items-suppliers', this.loadLineItemsSupplierData, this);
            mediator.on('product:update:line-items', this.updateLineItems, this);

        },

        /**
         * @param {Function} callback
         */
        getLineItemsSuppliers: function(callback) {
            callback(this.options.suppliers);
        },

        /**
         * @param {Array} items
         * @param {Function} callback
         */
        loadLineItemsSupplierData: function(items, callback) {
            var params = {
                supplier_ids: items
            };

            var supplier = this._getSupplier();
            if (supplier.length !== 0) {
                params = _.extend(params, {supplier: supplier});
            }

            $.ajax({
                url: routing.generate(this.options.supplierDataRoute, params),
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
         * update line items with a not-salable class and show which line item
         * is not salable by displaying the error element
         * @param options
         */
        updateLineItems: function (options) {
            //todo
            if(null === options.salable) {
                return;
            }
            var $elm = options.elm;
            var $errorElm = $elm.find('td.order-line-item-notifications span.error');
            if(false === options.salable.value) {
                $errorElm.find('i').attr('data-content', options.salable.message);
                layout.initPopover($errorElm);
                $errorElm.show();
                if(!this.$confirm) {
                    this.handleConfirmation();
                }
            } else {
                $errorElm.hide();
                this.$confirm = false;
            }
        },

        /**
         * get sales supplier value
         * @returns {string}
         * @private
         */
        _getSupplier: function() {
            return this.$supplier.length !== 0 ? this.$supplier.val() : '';
        },

        /**
         * @returns {Array} suppliers
         */
        getItems: function() {
            var lineItems = this.$el.find('.marello-line-item');
            var items = [];

            _.each(lineItems, function(lineItem) {
                var $lineItem = $(lineItem);
                var supplierId = $lineItem.find('input[data-ftid$="_supplier"]')[0].value;
                if (supplierId.length === 0) {
                    return;
                }

                var quantityOfUnit = $lineItem.find('input[data-ftid$="_quantityOfUnit"]')[0].value;
                var priority = $lineItem.find('input[data-ftid$="_priority"]')[0].value;
                var cost = $lineItem.find('input[data-ftid$="_cost"]')[0].value;

                items.push({'supplier': supplierId, 'quantityOfUnit': quantityOfUnit, 'priority': priority, 'cost': cost});
            });

            return items;
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off('product:get:line-items-suppliers', this.getLineItemsSuppliers, this);
            mediator.off('product:load:line-items-suppliers', this.loadLineItemsSupplierData, this);
            mediator.off('product:update:line-items', this.updateLineItems, this);


            ProductSuppliersView.__super__.dispose.call(this);
        }
    });

    return ProductSuppliersView;
});
