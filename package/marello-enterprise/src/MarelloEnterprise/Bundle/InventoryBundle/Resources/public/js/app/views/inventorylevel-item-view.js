define(function(require) {
    'use strict';

    var InventoryLevelItemView,
        _ = require('underscore'),
        __ = require('orotranslation/js/translator'),
        $ = require('jquery'),
        DeleteConfirmation = require('oroui/js/delete-confirmation'),
        AbstractItemView = require('marellolayout/js/app/views/abstract-item-view');

    /**
     * @export marelloenterpriseinventory/js/app/views/inventorylevel-item-view
     * @extends marellolayout.app.views.AbstractItemView
     * @class marelloenterpriseinventory.app.views.InventoryLevelItemView
     */
    InventoryLevelItemView = AbstractItemView.extend({
        /**
         * @property {Object}
         */
        options: {
            deleteMessage: 'marelloenterprise.inventory.inventoryitem.inventory_levels.delete.confirmation',
            managedInventorySelector: 'input[name*=managedInventory]',
            quantitySelector: 'input[name*=quantity]',
            adjustmentOperatorSelector: 'select[name*=adjustmentOperator]'
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            InventoryLevelItemView.__super__.initialize.apply(this, arguments);
            var managedInventoryEl = $(this.$el).find(this.options.managedInventorySelector);
            $(managedInventoryEl).on('change', _.bind(this.onManagedInventoryChange, this));
            if (!$(managedInventoryEl).is(':disabled')) {
                $(managedInventoryEl).trigger('change');
            }
        },
        
        onManagedInventoryChange: function(e) {
            var quantityEl = $(this.$el).find(this.options.quantitySelector);
            var adjustmentOperatorEl = $(this.$el).find(this.options.adjustmentOperatorSelector);
            if ($(e.target).is(':checked')) {
                $(quantityEl).prop('disabled', false);
                $(adjustmentOperatorEl).prop('disabled', false);
                $(adjustmentOperatorEl).parent('div').removeClass('disabled');
            } else {
                $(quantityEl).prop('disabled', true);
                $(adjustmentOperatorEl).prop('disabled', true);
                $(adjustmentOperatorEl).parent('div').addClass('disabled')
            }
        },
        
        /**
         * remove single line item
         */
        removeRow: function() {
            if (!this.confirm) {
                this.confirm = new DeleteConfirmation({
                    content: __(this.options.deleteMessage)
                });
            }

            this.confirm
                .off('ok')
                .on('ok', _.bind(function() {
                    InventoryLevelItemView.__super__.removeRow.apply(this, arguments);
                }, this))
                .open();
        },
        
        dispose: function() {
            if (this.disposed) {
                return;
            }

            InventoryLevelItemView.__super__.dispose.call(this);
        }

    });

    return InventoryLevelItemView;
});

