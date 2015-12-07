define(function(require) {
    'use strict';

    var OrderItemsView,
        $ = require('jquery'),
        _ = require('underscore'),
        __ = require('orotranslation/js/translator'),
        DeleteConfirmation = require('oroui/js/delete-confirmation'),
        routing = require('routing'),
        mediator = require('oroui/js/mediator'),
        BaseView = require('oroui/js/app/views/base/view');

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
         * @property {Object}
         */
        $channelHistory: {'prev':null, 'current':null},

        /**
         * @property {Object}
         */
        $confirm: false,

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
            mediator.on('order:update:line-items', this.updateLineItems, this);

            this.$salesChannel.change(_.bind(function() {
                this.loadLineItemsPrices(this.getItems(), function(response) {
                    mediator.trigger('order:refresh:line-items-prices', response);
                });
                this.setChannelHistory(this._getSalesChannel());
            }, this));

            this.initChannelHistory();
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
         * initialize channel history (current and prev selected channels)
         */
        initChannelHistory: function () {
            this.setChannelHistory(this._getSalesChannel());
        },

        /**
         * update the current and prev channels
         * @param channel
         */
        setChannelHistory: function(channel) {
            this.$channelHistory.prev = (this.$channelHistory.current === null) ? parseInt(channel) : this.$channelHistory.current;
            this.$channelHistory.current = parseInt(channel);
        },

        /**
         * update line items with a not-salable class and show which line item
         * is not salable by displaying the error element
         * @param options
         */
        updateLineItems: function (options) {
            if(null === options.salable) {
                return;
            }
            var $elm = options.elm;
            var $errorElm = $elm.find('td.order-line-item-notifications span.error');
            if(false === options.salable) {
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
         * show confirmation that once you change the channel,
         * you will not be able to save the order. On cancel
         * change the channel back to it's previous selected channel
         */
        handleConfirmation: function() {
            var _self = this;
            _self.$confirm = true;
            var message = __('You cannot save this order, there are errors in the Order Items, please correct them before saving the order');
            var confirm = new DeleteConfirmation({
                content: message,
                okText: __('OK')
            });

            confirm.open();
            confirm.on('cancel', function(){
                _self.$confirm = false;
                _self.$salesChannel.val(_self.$channelHistory.prev).trigger('change');
            });

            confirm.on('close', function(){
                _self.$confirm = false;
            });

            confirm.on('ok', function(){
                _self.$confirm = false;
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

        /**
         * get sales channel value
         * @returns {string}
         * @private
         */
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
            mediator.off('order:update:line-items', this.updateLineItems, this);

            OrderItemsView.__super__.dispose.call(this);
        }
    });

    return OrderItemsView;
});
