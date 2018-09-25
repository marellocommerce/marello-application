define(function(require) {
    'use strict';

    var OrderItemsView,
        $ = require('jquery'),
        _ = require('underscore'),
        __ = require('orotranslation/js/translator'),
        DeleteConfirmation = require('oroui/js/delete-confirmation'),
        routing = require('routing'),
        mediator = require('oroui/js/mediator'),
        layout = require('oroui/js/layout'),
        AbstractItemsView = require('marellolayout/js/app/views/abstract-items-view');

    /**
     * @export marelloorder/js/app/views/order-items-view
     * @extends marellolayout.app.views.AbstractItemsView
     * @class marelloorder.app.views.OrderItemsView
     */
    OrderItemsView = AbstractItemsView.extend({
        /**
         * @property {Object}
         */
        options: {
            data: {},
            route: 'marello_order_item_data'
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
            OrderItemsView.__super__.initialize.apply(this, arguments);
            mediator.on('order:form-changes:trigger', this.loadingStart, this);
        },

        /**
         * Doing something after loading child components
         */
        handleLayoutInit: function() {
            OrderItemsView.__super__.handleLayoutInit.apply(this, arguments);
            this.$salesChannel = this.$form.find(':input[data-ftid="' + this.$form.attr('name') + '_salesChannel"]');

            mediator.on('order:get:line-items-data', this.getLineItemsData, this);
            mediator.on('order:form-changes:load', this.loadLineItemsData, this);
            mediator.on('order:update:line-items', this.updateLineItems, this);

            this.$salesChannel.change(_.bind(function() {
                this.setChannelHistory(this._getSalesChannel());
                this.initLineItemAdditionalData();
            }, this));

            this.initChannelHistory();
            this.initLineItemAdditionalData();
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
                title: __('Your order needs changes'),
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
         * @param {Function} callback
         */
        getLineItemsData: function(callback) {
            callback(this.options.data);
        },

        /**
         * @param {Array} response
         */
        loadLineItemsData: function(response) {
            this.loadingEnd();
            if (response === undefined || response['items'] === undefined || response['items'].length == 0) {
                return;
            }
            mediator.trigger('order:refresh:line-items', response['items']);
        },

        /**
         * trigger additional data changes when the form is loaded
         */
        initLineItemAdditionalData: function() {
            // for some reason we need to trigger the Billing and Shipping addresses here in order to 'reload' the
            // customer addresses in the select boxes...
            mediator.trigger('order:form-changes:trigger', {updateFields: ['billingAddress', 'shippingAddress','totals']});

            if (this.getItems().length === 0 || this._getSalesChannel().length === 0 ) {
                return;
            }
            
            mediator.trigger('order:form-changes:trigger', {updateFields: ['items', 'inventory','totals', 'possible_shipping_methods']});
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
            var lineItems = this.$el.find('.marello-line-item');
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
         * Show loading view
         */
        loadingStart: function(e) {
            if (e.updateFields !== undefined && _.contains(e.updateFields, 'items') !== true) {
                return;
            }
            OrderItemsView.__super__.loadingStart.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off('order:get:line-items-data', this.getLineItemsData, this);
            mediator.off('order:form-changes:load', this.loadLineItemsData, this);
            mediator.off('order:update:line-items', this.updateLineItems, this);
            mediator.off('order:form-changes:trigger', this.loadingStart, this);

            OrderItemsView.__super__.dispose.call(this);
        }
    });

    return OrderItemsView;
});
