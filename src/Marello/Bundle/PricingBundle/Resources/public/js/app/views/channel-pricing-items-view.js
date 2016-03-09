define(function(require) {
    'use strict';

    var PricingItemsView,
        $ = require('jquery'),
        __ = require('orotranslation/js/translator'),
        routing = require('routing'),
        mediator = require('oroui/js/mediator'),
        DeleteConfirmation = require('oroui/js/delete-confirmation'),
        AbstractItemsView = require('marellolayout/js/app/views/abstract-items-view');

    /**
     * @export marellopricing/js/app/views/channel-pricing-items-view
     * @extends marellolayout.app.views.AbstractItemsView
     * @class marellopricing.app.views.PricingItemsView
     */
    PricingItemsView = AbstractItemsView.extend({
        /**
         * @property {Object}
         */
        options: {
            currencyRoute: 'marello_pricing_currency_by_channel'
        },
        /**
         * @property {jQuery}
         */
        $form: null,
        /**
         * @property {jQuery}
         */
        $sourceElement: null,
        /**
         * @property {jQuery}
         */
        $checkBoxElement: null,
        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            this.$sourceElement = options.el;
            this.$checkBoxElement = this.$sourceElement.find('#' + options.options.pricing_enable_id);
            this.delegate('click','#' + options.options.pricing_enable_id, this.enableHandler);
            mediator.on('pricing:load:line-item-currency', this.loadLineItemCurrency, this);
            PricingItemsView.__super__.initialize.apply(this, arguments);
        },

        /**
         * @param {Array} item
         * @param {Function} callback
         */
        loadLineItemCurrency: function(item, callback) {
            $.ajax({
                url: routing.generate(this.options.currencyRoute, item),
                type: 'GET',
                success: function(response) {
                    callback(response);
                },
                error: function(response) {
                    callback();
                }
            });
        },

        enableHandler: function() {
            if (this.$checkBoxElement.is(':checked')) {
                this.$sourceElement.addClass('pricing-enabled');
            } else {
                this.$sourceElement.removeClass('pricing-enabled');
                this.handleDeleteConfirmation();
            }
        },

        /**
         * @returns {Array} products
         */
        clearItems: function() {
            var lineItems = this.$sourceElement.find('.marello-line-item');
            _.each(lineItems, function(lineItem) {
                var $lineItem = $(lineItem);
                $lineItem.find('input[data-ftid$="_value"]')[0].value = 0;
            });

        },

        handleDeleteConfirmation: function() {
            var _self = this;
            var message = __('By disabling the Channel Pricing, all data from Channel Pricing will be lost upon saving. Are you sure?');
            var confirm = new DeleteConfirmation({
                content: message,
                okText: __('Yes, I\'m sure')
            });

            confirm.open();

            confirm.on('cancel', function() {
                _self.$sourceElement.addClass('pricing-enabled');
                _self.$checkBoxElement.prop('checked',true);
            });

            confirm.on('ok', function() {
                _self.clearItems();
            });
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off('pricing:load:line-item-currency', this.loadLineItemCurrency, this);

            PricingItemsView.__super__.dispose.call(this);
        }
    });

    return PricingItemsView;
});
