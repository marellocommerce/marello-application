define(function(require) {
    'use strict';

    var OrderItemView,
        $ = require('jquery'),
        _ = require('underscore'),
        mediator = require('oroui/js/mediator'),
        AbstractItemView = require('marellolayout/js/app/views/abstract-item-view');

    /**
     * @export marelloorder/js/app/views/order-item-view
     * @extends marellolayout.app.views.AbstractItemView
     * @class marelloorder.app.views.OrderItemView
     */
    OrderItemView = AbstractItemView.extend({
        options: {
            ftid: '',
            salable: null
        },

        /**
         * @property {Object}
         */
        itemIdentifier: null,

        /**
         * @property {Object}
         */
        data: {},

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            OrderItemView.__super__.initialize.apply(this, arguments);
        },

        /**
         * Doing something after loading child components
         */
        handleLayoutInit: function() {
            OrderItemView.__super__.handleLayoutInit.apply(this, arguments);
            this.initOrderItem();
        },

        /**
         * initialize item triggers and field events
         */
        initOrderItem: function() {
            this.addFieldEvents('product', this.updateOrderItemData);
            this.addFieldEvents('quantity', this.updateOrderItemData);
            mediator.trigger('order:get:line-items-data', _.bind(this.setOrderItemData, this));
            mediator.on('order:refresh:line-items', this.setOrderItemData, this);
        },

        /**
         * Trigger subtotals update
         */
        updateOrderItemData: function() {
            if (this.itemIdentifier &&
                this.itemIdentifier === this._getItemIdentifier()
            ) {
                this.setOrderItemData();
                return;
            }
            var productId = this._getProductId();

            if (productId.length === 0) {
                this.setOrderItemData({});
            } else {
                mediator.trigger('order:form-changes:trigger', {updateFields: ['items', 'totals', 'inventory', 'possible_shipping_methods']});
            }
        },

        setOrderItemData: function(data) {
            if (data === undefined || typeof(data) == 'undefined' || data.length == 0) {
                return;
            }

            var identifier = this._getItemIdentifier();
            if (identifier && data[identifier] !== undefined) {
                if(data[identifier].message !== undefined) {
                    this.data = {};
                    this.setRowTotals();
                    this.options.salable = {value: false, message: data[identifier].message};
                } else {
                    this.data = data[identifier] || {};
                    this.options.salable = {value: true, message: ''};
                }

                mediator.trigger('order:update:line-items', {'elm': this.$el, 'salable': this.options.salable},this);
            } else {
                this.data = {};
            }

            var $priceValue = parseFloat(this.getPriceValue()).toFixed(2);
            if($priceValue === "NaN" || $priceValue === null) {
                $priceValue = '';
            }


            this.fieldsByName.price.val($priceValue);
            this.fieldsByName.taxCode.val(this.getTaxCode());

            this.setRowTotals();
            this.setAvailableInventory();
        },

        /**
         * @returns {String|Null}
         */
        getPriceValue: function() {
            return !_.isEmpty(this.data['price']) ? this.data['price'].value : null;
        },

        /**
         * @returns {String|Null}
         */
        getTaxCode: function() {
            return !_.isEmpty(this.data['tax_code']) ? this.data['tax_code'].code : null;
        },

        /**
         * @returns {Array|Null}
         */
        getRowTotals: function() {
            return !_.isEmpty(this.data['row_totals']) ? this.data['row_totals'] : null;
        },

        /**
         * @returns {Array|Null}
         */
        getProductInventory: function() {
            return !_.isEmpty(this.data['inventory']) ? this.data['inventory'].value : null;
        },

        /**
         * Set row totals
         */
        setRowTotals: function() {
            var row_totals = this.getRowTotals();
            if (row_totals === null) {
                this.fieldsByName.tax.val('');
                this.fieldsByName.rowTotalExclTax.val('');
                this.fieldsByName.rowTotalInclTax.val('');
            } else {
                var taxAmount = parseFloat(row_totals.taxAmount).toFixed(2);
                var taxExcl = parseFloat(row_totals.excludingTax).toFixed(2);
                var taxIncl = parseFloat(row_totals.includingTax).toFixed(2);
                this.fieldsByName.tax.val(taxAmount);
                this.fieldsByName.rowTotalExclTax.val(taxExcl);
                this.fieldsByName.rowTotalInclTax.val(taxIncl);
            }
        },

        setAvailableInventory: function() {
            if (this.getProductInventory() === null) {
                return
            }

            this.fieldsByName.availableInventory.val(this.getProductInventory());
        },

        /**
         * @returns {String|Null}
         * @private
         */
        _getItemIdentifier: function() {
            var productId = this._getProductId();

            return productId.length === 0 ? null : 'product-id-' + productId;
        },

        /**
         * @inheritDoc
         */
        removeRow: function() {
            OrderItemView.__super__.removeRow.call(this);
            mediator.trigger('order:form-changes:trigger', {updateFields: ['totals', 'possible_shipping_methods']});
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off('order:refresh:line-items', this.setOrderItemData, this);

            OrderItemView.__super__.dispose.call(this);
        }
    });

    return OrderItemView;
});
