define(function(require) {
    'use strict';

    const
        $ = require('jquery'),
        _ = require('underscore'),
        mediator = require('oroui/js/mediator'),
        AbstractItemView = require('marellolayout/js/app/views/abstract-item-view');

    /**
     * @export marellosupplier/js/app/views/product-supplier-view
     * @extends marellolayout.app.views.AbstractItemView
     * @class marellosupplier.app.views.ProductSupplierView
     */
    const ProductSupplierView = AbstractItemView.extend({
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
                if (this.fieldsByName.canDropship !== undefined) {
                    this.fieldsByName.canDropship
                        .prop('checked', this.options.canDropship);
                    if (this.options.canDropship) {
                        $(this.fieldsByName.canDropship).parent().show();
                    } else {
                        $(this.fieldsByName.canDropship).parent().hide();
                    }
                }
            }

            if (data.currency.length !== 0) {
                this.options.currency = ' ' + data.currency;
            }

            this.fieldsByName.priority
                .val(this.options.priority);

            var parent = $(this.fieldsByName.cost).parent();
            parent.contents().filter(function(){
                return (this.nodeType == 3);
            }).remove();
            parent.append(this.options.currency);
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
