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
         * @property {Object}
         */
        taxPercentage: 21,

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
            this.addFieldEvents('quantity', this.updateRowTotals);
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
            var quantity = this.fieldsByName.quantity.val();

            if (productId.length === 0) {
                this.setOrderItemData({});
            } else {
                mediator.trigger(
                    'order:load:line-items-data',
                    [{'product': productId, 'qty': quantity}],
                    _.bind(this.setOrderItemData, this)
                );
            }
        },

        setOrderItemData: function(data) {
            if (data === undefined || typeof(data) == 'undefined' || data.length == 0) {
                return;
            }
            var identifier = this._getItemIdentifier();
            if (identifier) {
                if(data[identifier].message !== undefined) {
                    this.data = {};
                    this.updateRowTotals();
                    this.options.salable = {value: false, message: data[identifier].message};
                } else {
                    this.data = data[identifier] || {};
                    this.options.salable = {value: true, message: ''};
                }
            } else {
                this.data = {};
            }
            mediator.trigger('order:update:line-items', {'elm': this.$el, 'salable': this.options.salable},this);

            var $priceValue = parseFloat(this.getPriceValue()).toFixed(2);
            if($priceValue === "NaN" || $priceValue === null) {
                $priceValue = '';
            }

            this.fieldsByName.price.val($priceValue);
            this.fieldsByName.taxCode.val(this.getTaxCode());

            this.updateRowTotals();
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
         * update row totals
         */
        updateRowTotals: function() {
            var $price = this.getPriceValue();
            var $rowTotalExclTax = '';
            var $rowTotalInclTax = '';
            var $tax = '';
            if($price) {
                var $quantity = this.fieldsByName.quantity.val();
                $rowTotalInclTax = parseFloat($price * $quantity).toFixed(2);
                var $priceExcl = (($rowTotalInclTax / (this.taxPercentage + 100)) * 100);
                $tax = parseFloat(Math.round($rowTotalInclTax - $priceExcl)).toFixed(2);
                $rowTotalExclTax = ($rowTotalInclTax - $tax).toFixed(2);
            }

            this.fieldsByName.tax.val($tax);
            this.fieldsByName.rowTotalExclTax.val($rowTotalExclTax);
            this.fieldsByName.rowTotalInclTax.val($rowTotalInclTax);
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
        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off('order:refresh:line-items-price', this.setPrice, this);

            OrderItemView.__super__.dispose.call(this);
        }
    });

    return OrderItemView;
});
