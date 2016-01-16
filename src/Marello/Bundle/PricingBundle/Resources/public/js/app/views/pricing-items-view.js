define(function(require) {
    'use strict';

    var PricingItemsView,
        $ = require('jquery'),
        __ = require('orotranslation/js/translator'),
        DeleteConfirmation = require('oroui/js/delete-confirmation'),
        AbstractItemsView = require('marellolayout/js/app/views/abstract-items-view');

    /**
     * @export marellopricing/js/app/views/pricing-items-view
     * @extends marellolayout.app.views.AbstractItemsView
     * @class marellopricing.app.views.PricingItemsView
     */
    PricingItemsView = AbstractItemsView.extend({
        /**
         * @property {Object}
         */
        options: {},
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
            PricingItemsView.__super__.initialize.apply(this, arguments);
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

            PricingItemsView.__super__.dispose.call(this);
        }
    });

    return PricingItemsView;
});
