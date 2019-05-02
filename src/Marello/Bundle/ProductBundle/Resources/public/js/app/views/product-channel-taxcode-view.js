define(function(require) {
    'use strict';

    var ProductChannelTaxView,
        $ = require('jquery'),
        _ = require('underscore'),
        mediator = require('oroui/js/mediator'),
        AbstractItemView = require('marellolayout/js/app/views/abstract-item-view');

    /**
     * @export marelloproduct/js/app/views/product-channel-taxcode-view
     * @extends marellolayout.app.views.AbstractItemView
     * @class marelloproduct.app.views.ProductChannelTaxView
     */
    ProductChannelTaxView = AbstractItemView.extend({
        options: {
            priority: 0,
            canDropship: false
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            ProductChannelTaxView.__super__.initialize.apply(this, arguments);
        }

    });

    return ProductChannelTaxView;
});
