define(function(require) {
    'use strict';

    var OrderItemsView;
    var $ = require('jquery');
    var _ = require('underscore');
    var routing = require('routing');
    var mediator = require('oroui/js/mediator');
    var BaseView = require('oroui/js/app/views/base/view');

    /**
     * @export marelloorder/js/app/views/order-items-view
     * @extends oroui.app.views.base.View
     * @class marelloorder.app.views.OrderItemsView
     */
    OrderItemsView = BaseView.extend({
        /**
         * @property {Object}
         */
        options: {
            prices: {},
            pricesRoute: 'marello_pricing_price_by_channel',
        },

        /**
         * @property {jQuery}
         */
        $form: null,

        /**
         * @property {jQuery}
         */
        $salesChannel: null,

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            this.initLayout().done(_.bind(this.handleLayoutInit, this));
            this.delegate('click', '.add-line-item', this.addRow);
        },

        /**
         * Doing something after loading child components
         */
        handleLayoutInit: function() {
            this.$form = this.$el.closest('form');
            this.$salesChannel = this.$form.find(':input[data-ftid="' + this.$form.attr('name') + '_salesChannel"]');
            this.$el.find('.add-list-item').mousedown(function(e) {
                $(this).click();
            });

            mediator.on('order:get:line-items-prices', this.getLineItemsPrices, this);
            mediator.on('order:load:line-items-prices', this.loadLineItemsPrices, this);

            this.$salesChannel.change(_.bind(function() {
                this.loadLineItemsPrices(this.getItems(), function(response) {
                    mediator.trigger('order:refresh:line-items-prices', response);
                });
            }, this));
        },

        /**
         * handle index and html for the collection container
         * @param $listContainer
         * @returns {{nextIndex: *, nextItemHtml: *}}
         */
        getCollectionInfo: function($listContainer) {
            var index = $listContainer.data('last-index') || $listContainer.children().length;

            var prototypeName = $listContainer.attr('data-prototype-name') || '__name__';
            var html = $listContainer.attr('data-prototype').replace(new RegExp(prototypeName, 'g'), index);
            return {
                nextIndex: index,
                nextItemHtml: html
            };
        },

        /**
         * handle add button
         */
        addRow: function() {
            var _self = this.$el.find('.add-line-item');
            var containerSelector = $(_self).data('container') || '.collection-fields-list';
            var $listContainer = this.$el.find('.row-oro').find(containerSelector).first();
            var collectionInfo = this.getCollectionInfo($listContainer);
            $listContainer.append(collectionInfo.nextItemHtml)
                .trigger('content:changed')
                .data('last-index', collectionInfo.nextIndex + 1);

            $listContainer.find('input.position-input').each(function(i, el) {
                $(el).val(i);
            });
        },

        /**
         * @returns {Array} products
         */
        getProductsId: function() {
            var products = this.$el.find('input[data-ftid$="_product"]');
            products = _.filter(products, function(product) {
                return product.value.length > 0;
            });
            products = _.map(products, function(product) {
                return product.value;
            });
            return products;
        },

        /**
         * @param {Function} callback
         */
        getLineItemsPrices: function(callback) {
            callback(this.options.prices);
        },

        /**
         * @param {Array} items
         * @param {Function} callback
         */
        loadLineItemsPrices: function(items, callback) {
            var params = {
                product_ids: items
            };

            var salesChannel = this._getSalesChannel();
            if (salesChannel.length !== 0) {
                params = _.extend(params, {salesChannel: salesChannel});
            }

            $.ajax({
                url: routing.generate(this.options.pricesRoute, params),
                type: 'GET',
                success: function(response) {
                    callback(response);
                },
                error: function(response) {
                    callback();
                }
            });
        },

        _getSalesChannel: function() {
            return this.$salesChannel.length !== 0 ? this.$salesChannel.val() : '';
        },

        /**
         * @returns {Array} products
         */
        getItems: function() {
            var lineItems = this.$el.find('.order-line-item');
            var items = [];

            _.each(lineItems, function(lineItem) {
                var $lineItem = $(lineItem);
                var productId = $lineItem.find('input[data-ftid$="_product"]')[0].value;
                if (productId.length === 0) {
                    return;
                }

                var quantity = $lineItem.find('input[data-ftid$="_quantity"]')[0].value;

                items.push({'product': productId, 'qty': quantity});
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

            mediator.off('order:get:line-items-prices', this.getLineItemsPrices, this);
            mediator.off('order:load:line-items-prices', this.loadLineItemsPrices, this);

            OrderItemsView.__super__.dispose.call(this);
        }
    });

    return OrderItemsView;
});
