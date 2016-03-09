define(function(require) {
    'use strict';

    var ChannelPricingItemView,
        $ = require('jquery'),
        _ = require('underscore'),
        mediator = require('oroui/js/mediator'),
        AbstractItemView = require('marellolayout/js/app/views/abstract-item-view');

    /**
     * @export marellopricing/js/app/views/channel-pricing-item-view
     * @extends marellolayout.app.views.AbstractItemView
     * @class marellopricing.app.views.ChannelPricingItemView
     */
    ChannelPricingItemView = AbstractItemView.extend({
        /**
         * @property {Object}
         */
        options: {
            ftid: ''
        },

        /**
         * @property {Object}
         */
        currencyIdentifier: null,

        /**
         * @property {Object}
         */
        currencyData: null,

        /**
         * @property {jQuery}
         */
        $currencySelector: null,

        /**
         * @property string
         */
        currencySelectorClass: 'span.currency',

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            ChannelPricingItemView.__super__.initialize.apply(this, arguments);
            this.$currencySelector = this.$el.find(this.currencySelectorClass);
        },

        /**
         * Doing something after loading child components
         */
        handleLayoutInit: function() {
            ChannelPricingItemView.__super__.handleLayoutInit.apply(this, arguments);
            this.initCurrency();
        },

        /**
         * initialize triggers and field events
         */
        initCurrency: function() {
            this.addFieldEvents('channel', this.updateCurrency);
        },

        /**
         * Trigger currency update
         */
        updateCurrency: function() {
            if (this.currencyIdentifier &&
                this.currencyIdentifier === this._getCurrencyIdentifier()
            ) {
                this.setCurrency();
                return;
            }
            var channelId = this._getSalesChannelId();

            if (channelId.length === 0) {
                this.setCurrency({});
            } else {
                mediator.trigger(
                    'pricing:load:line-item-currency',
                    {'salesChannel': channelId},
                    _.bind(this.setCurrency, this)
                );
            }
        },

        /**
         * @param {Object} prices
         */
        setCurrency: function(currency) {
            if (currency === undefined) {
                return;
            }

            var identifier = this._getCurrencyIdentifier();
            if (identifier) {
                if(currency[identifier].message == undefined) {
                    this.currencyData = currency[identifier] || {};
                }
            }
            // update currency hidden field
            this.fieldsByName.currency
                .val(this.currencyData.currencyCode);

            // update display value
            $(this.$currencySelector).html(this._formatCurrency());
        },

        /**
         * format currency data
         * @returns {string}
         * @private
         */
        _formatCurrency: function() {
           return this.currencyData.currencyCode + ' ' + '(' + this.currencyData.currencySymbol + ')'
        },

        /**
         * @returns {String|Null}
         * @private
         */
        _getCurrencyIdentifier: function() {
            var channelId = this._getSalesChannelId();

            return channelId.length === 0 ? null : 'currency-' + channelId;
        },

        /**
         * get sales selected sales channel id
         * @returns {string}
         * @private
         */
        _getSalesChannelId: function() {
            return this.fieldsByName.hasOwnProperty('channel') ? this.fieldsByName.channel.val() : '';
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            ChannelPricingItemView.__super__.dispose.call(this);
        }
    });

    return ChannelPricingItemView;
});
