define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const mediator = require('oroui/js/mediator');
    const AbstractItemView = require('marellolayout/js/app/views/abstract-item-view');

    /**
     * @export marellorefund/js/app/views/refund-view
     * @extends marellolayout.app.views.AbstractItemView
     * @class marellorefund.app.views.RefundItemView
     */
    const RefundItemView = AbstractItemView.extend({
        options: {
            selectors: {
                totals: '[data-totals-container]'
            }
        },

        /**
         * @property {Object}
         */
        data: {},

        /**
         * @property {jQuery}
         */
        $totals: null,

        /**
         * @property {Array}
         */
        items: [],

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            RefundItemView.__super__.initialize.apply(this, arguments);
        },

        /**
         * Doing something after loading child components
         */
        handleLayoutInit: function() {
            RefundItemView.__super__.handleLayoutInit.apply(this, arguments);
            this.initRefundItem();
        },

        /**
         * initialize item triggers and field events
         */
        initRefundItem: function() {
            this.addFieldEvents('quantity', this.updateRefundItemData);
            mediator.trigger('refund:get:line-items-data', _.bind(this.setOrderItemData, this));
            mediator.on('refund:refresh:line-items', this.setOrderItemData, this);
        },

        /**
         * Trigger subtotals update
         */
        updateRefundItemData: function() {
            let amount = this.$el.find('td.price-field div').text() * this.fieldsByName.quantity.val();
            this.fieldsByName.refundAmount.val(amount);
            mediator.trigger('refund:item-data:trigger', {updateFields: ['items', 'totals']});
            mediator.trigger('refund:form-changes:trigger', {updateFields: ['items', 'totals']});
        },

        /**
         * @param {Object} data
         */
        setTotals: function(data) {
            var totals = _.defaults(data, {totals: {total: {}, subtotals: {}}}).totals;

            this.render(totals);
        },

        /**
         * Render totals
         *
         * @param {Object} totals
         */
        render: function(totals) {
            this.items = [];
           /* if (totals !== undefined && totals.subtotals !== undefined && totals.total !== undefined) {
                _.each(totals.subtotals, _.bind(this.pushItem, this));

                this.pushItem(totals.total);
            }
            var items = _.filter(this.items);
*/
            this.$totals.html('sdfsdf');

  /*          this.items = [];*/
        },
        /**
         * @inheritDoc
         * @param data
         */
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
            this.fieldsByName.productUnit.val(this.getProductUnit());

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
        getRefundValue: function() {
            return !_.isEmpty(this.data['refundAmount']) ? this.data['refundAmount'].value : null;
        },

        /**
         * @returns {String|Null}
         */
        getQuantityValue: function() {
            return !_.isEmpty(this.data['quantity']) ? this.data['quantity'].value : null;
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
            if (this.getRowItemIdentifier() === null) {
                return null;
            }
            if (_.isEmpty(this.data['row_totals'])) {
                return null;
            }
            return !_.isEmpty(this.data['row_totals'][this.getRowItemIdentifier()]) ? this.data['row_totals'][this.getRowItemIdentifier()] : null;
        },

        /**
         * @returns {Array|Null}
         */
        getProductUnit: function() {
            return !_.isEmpty(this.data['product_unit']) ? this.data['product_unit'].unit : null;
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
                return;
            }

            this.fieldsByName.availableInventory.val(this.getProductInventory());
        },

        /**
         * @returns {String|Null}
         * @private
         */
        _getItemIdentifier: function() {
            var productId = this.getProductId();
            if (productId.length === 0) {
                return null;
            }

            return 'product-id-' + productId;
        },

        /**
         * @inheritDoc
         */
        removeRow: function() {
            RefundItemView.__super__.removeRow.call(this);
            mediator.trigger('order:form-changes:trigger', {updateFields: ['totals', 'possible_shipping_methods', 'possible_payment_methods']});
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off('refund:refresh:line-items', this.setOrderItemData, this);

            RefundItemView.__super__.dispose.call(this);
        }
    });

    return RefundItemView;
});
