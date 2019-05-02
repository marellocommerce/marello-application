define(function(require) {
    'use strict';

    var ProductSupplierView,
        $ = require('jquery'),
        _ = require('underscore'),
        mediator = require('oroui/js/mediator'),
        AbstractItemView = require('marellolayout/js/app/views/abstract-item-view');

    /**
     * @export marellosupplier/js/app/views/product-supplier-view
     * @extends marellolayout.app.views.AbstractItemView
     * @class marellosupplier.app.views.ProductSupplierView
     */
    ProductSupplierView = AbstractItemView.extend({
        options: {
            priority: 0,
            canDropship: false
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            ProductSupplierView.__super__.initialize.apply(this, arguments);
        },

        /**
         * Doing something after loading child components
         */
        handleLayoutInit: function() {
            ProductSupplierView.__super__.handleLayoutInit.apply(this, arguments);
            this.initSupplierDataTriggers();
        },

        /**
         * initialize supplier data triggers and field events
         */
        initSupplierDataTriggers: function() {
            this.addFieldEvents('supplier', this.loadSupplierDefaultData);
        },

        /**
         * Load supplier default data
         * fetch the supplier id from the row
         * trigger mediator to get the data
         */
        loadSupplierDefaultData: function() {
            var supplierId = this._getSupplierId();
            if (supplierId.length === 0) {
                return;
            }

            mediator.trigger(
                'supplier:load:line-items-supplier',
                {'supplier_id': supplierId},
                _.bind(this.setSupplierDefaultData, this)
            );

        },

        /**
         * Set default for suppliers
         * @param data
         */
        setSupplierDefaultData: function (data)
        {
            if (data === undefined) {
                return;
            }

            if (data.priority.length !== 0) {
                this.options.priority = data.priority;
            }

            if (data.canDropship.length !== 0) {
                this.options.canDropship = data.canDropship;
            }

            this.fieldsByName.priority
                .val(this.options.priority);

            this.fieldsByName.canDropship
                .prop('checked', this.options.canDropship);
        },

        /**
         * @returns {String}
         * @private
         */
        _getSupplierId: function() {
            return this.fieldsByName.hasOwnProperty('supplier') ? this.fieldsByName.supplier.val() : '';
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            ProductSupplierView.__super__.dispose.call(this);
        }
    });

    return ProductSupplierView;
});
