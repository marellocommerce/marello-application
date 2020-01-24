define(function(require) {
    'use strict';

    var ProductChannelTaxCodesView,
        $ = require('jquery'),
        _ = require('underscore'),
        routing = require('routing'),
        mediator = require('oroui/js/mediator'),
        AbstractItemsView = require('marellolayout/js/app/views/abstract-items-view');

    /**
     * @export marelloproduct/js/app/views/product-channel-taxcodes-view
     * @extends marellolayout.app.views.AbstractItemsView
     * @class marelloproduct.app.views.ProductChannelTaxCodesView
     */
    ProductChannelTaxCodesView = AbstractItemsView.extend({
        /**
         * @property {Object}
         */
        options: {
            suppliers: {},
            supplierDataRoute: 'marello_supplier_supplier_get_default_data',
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            ProductChannelTaxCodesView.__super__.initialize.apply(this, arguments);
        }

    });

    return ProductChannelTaxCodesView;
});
