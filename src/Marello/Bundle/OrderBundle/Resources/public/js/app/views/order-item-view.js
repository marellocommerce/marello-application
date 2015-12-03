define(function(require) {
    'use strict';

    var OrderItemView,
        $ = require('jquery'),
        _ = require('underscore'),
        mediator = require('oroui/js/mediator'),
        BaseView = require('oroui/js/app/views/base/view');

    /**
     * @export marelloorder/js/app/views/order-item-view
     * @extends oroui.app.views.base.View
     * @class marelloorder.app.views.OrderItemView
     */
    OrderItemView = BaseView.extend({
        options: {
            ftid: ''
        },

        /**
         * @property {jQuery}
         */
        $fields: null,

        /**
         * @property {Object}
         */
        fieldsByName: null,

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
        change: {},

        /**
         * @property {Object}
         */
        taxPercentage: 21,

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            if (!this.options.ftid) {
                this.options.ftid = this.$el.data('content').toString()
                    .replace(/[^a-zA-Z0-9]+/g, '_').replace(/_+$/, '');
            }

            this.initLayout().done(_.bind(this.handleLayoutInit, this));
            this.delegate('click', '.remove-line-item', this.removeRow);
        },

        /**
         * Doing something after loading child components
         */
        handleLayoutInit: function() {
            var self = this;
            this.$fields = this.$el.find(':input[data-ftid]');
            this.fieldsByName = {};
            this.$fields.each(function() {
                var $field = $(this);
                var name = self.normalizeName($field.data('ftid').replace(self.options.ftid + '_', ''));
                self.fieldsByName[name] = $field;
            });

            this.initPrice();
        },

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
            if (prices === undefined) {
                return;
            }
            var identifier = this._getPriceIdentifier();
            if (identifier) {
                if(prices[identifier].message) {
                    this.price = {};
                    //TODO:: disable saving since this product cannot be sold in this channel...
                    console.log(prices[identifier].message);
                } else {
                    this.price = prices[identifier] || {};
                }
            } else {
                this.price = {};
            }

            this.priceIdentifier = identifier;

            var $priceValue = parseFloat(this.getPriceValue()).toFixed(2);
            if($priceValue === "NaN" || $priceValue === null) {
                $priceValue = parseFloat(0).toFixed(2);
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
            var $quantity = this.fieldsByName.quantity.val();
            var $totalPrice = parseFloat($price * $quantity).toFixed(2);
            var $priceExcl = (($totalPrice / (this.taxPercentage + 100)) * 100);
            var $tax = parseFloat(Math.round($totalPrice - $priceExcl)).toFixed(2);
            this.fieldsByName.tax.val($tax);
            this.fieldsByName.totalPrice.val($totalPrice);

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
         * @returns {String}
         * @private
         */
        _getProductId: function() {
            return this.fieldsByName.hasOwnProperty('product') ? this.fieldsByName.product.val() : '';
        },

        /**
         * @returns {String|Null}
         */
        getPriceValue: function() {
            return !_.isEmpty(this.price) ? this.price.value : null;
        },

        /**
         * @param {String} field
         * @param {Function} callback
         */
        addFieldEvents: function(field, callback) {
            this.fieldsByName[field].change(_.bind(function() {
                if (this.change[field]) {
                    clearTimeout(this.change[field]);
                }

                callback.call(this);
            }, this));

            this.fieldsByName[field].keyup(_.bind(function() {
                if (this.change[field]) {
                    clearTimeout(this.change[field]);
                }

                this.change[field] = setTimeout(_.bind(callback, this), 1500);
            }, this));
        },

        /**
         * Convert name with "_" to name with upper case, example: some_name > someName
         *
         * @param {String} name
         *
         * @returns {String}
         */
        normalizeName: function(name) {
            name = name.split('_');
            for (var i = 1, iMax = name.length; i < iMax; i++) {
                name[i] = name[i][0].toUpperCase() + name[i].substr(1);
            }
            return name.join('');
        },

        removeRow: function() {
            this.$el.trigger('content:remove');
            this.remove();
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
