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
        priceIdentifier: null,

        /**
         * @property {Object}
         */
        price: {},

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
            this.initPrice();
        },

        /**
         * initialize price triggers and field events
         */
        initPrice: function() {
            this.addFieldEvents('product', this.updatePrice);
            this.addFieldEvents('quantity', this.updateRowTotals);
            mediator.trigger('order:get:line-items-prices', _.bind(this.setPrice, this));
            mediator.on('order:refresh:line-items-prices', this.setPrice, this);
        },

        /**
         * Trigger subtotals update
         */
        updatePrice: function() {
            if (this.priceIdentifier &&
                this.priceIdentifier === this._getPriceIdentifier()
            ) {
                this.setPrice();
                return;
            }
            var productId = this._getProductId();
            var quantity = this.fieldsByName.quantity.val();

            if (productId.length === 0) {
                this.setPrice({});
            } else {
                mediator.trigger(
                    'order:load:line-items-prices',
                    [{'product': productId, 'qty': quantity}],
                    _.bind(this.setPrice, this)
                );
            }
        },

        /**
         * @param {Object} prices
         */
        setPrice: function(prices) {
            if (prices === undefined || typeof(prices) == 'undefined' || prices.length == 0) {
                return;
            }
            var identifier = this._getPriceIdentifier();
            if (identifier) {
                if(prices[identifier].message !== undefined) {
                    this.price = '';
                    this.updateRowTotals();
                    this.options.salable = {value: false, message: prices[identifier].message};
                } else {
                    this.price = prices[identifier] || {};
                    this.options.salable = {value: true, message: ''};
                }
            } else {
                this.price = {};
            }
            mediator.trigger('order:update:line-items', {'elm': this.$el, 'salable': this.options.salable},this);

            this.priceIdentifier = identifier;

            var $priceValue = parseFloat(this.getPriceValue()).toFixed(2);
            if($priceValue === "NaN" || $priceValue === null) {
                $priceValue = '';
            }

            this.fieldsByName.price
                .val($priceValue);

            this.updateRowTotals();
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
        _getPriceIdentifier: function() {
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
