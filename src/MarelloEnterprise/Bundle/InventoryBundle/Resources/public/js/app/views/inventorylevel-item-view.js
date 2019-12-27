define(function(require) {
    'use strict';

    const
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
    const InventoryLevelItemView = AbstractItemView.extend({
        /**
         * @property {Object}
         */
        options: {
            deleteMessage: 'marelloenterprise.inventory.inventoryitem.inventory_levels.delete.confirmation',
            deleteForbiddenTitle: 'marelloenterprise.inventory.inventoryitem.inventory_levels.delete.forbidden.title',
            deleteForbiddenMessage: 'marelloenterprise.inventory.inventoryitem.inventory_levels.delete.forbidden',
            managedInventorySelector: 'input[name*=managedInventory]',
            quantitySelector: 'input[name*=quantity]',
            adjustmentOperatorSelector: 'select[name*=adjustmentOperator]',
            enableBatchInventorySelector: 'input[name*=enableBatchInventory]'
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            this.options.enableBatchInventory = $(document).find(this.options.enableBatchInventorySelector).is(':checked');
            InventoryLevelItemView.__super__.initialize.apply(this, arguments);
            if (this.options.enableBatchInventory === true) {
                $(this.$el).find('.inventorylevel-managed-inventory').find('.fields-row').remove();
                if ($(this.$el).find(this.options.adjustmentOperatorSelector).length > 0) {
                    $(this.$el).find('.inventorylevel-adjustment').find('.fields-row').remove();
                }
            } else {
                var managedInventoryEl = $(this.$el).find(this.options.managedInventorySelector);
                $(managedInventoryEl).on('change', _.bind(this.onManagedInventoryChange, this));
                if (!$(managedInventoryEl).is(':disabled')) {
                    $(managedInventoryEl).trigger('change');
                }
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
            var invLevQty = Number($(this.$el).find('.inventorylevel-quantity').find('div.fields-row').text());
            if (invLevQty <= 0) {
                if (!this.deleteConfirm) {
                    this.deleteConfirm = new DeleteConfirmation({
                        content: __(this.options.deleteMessage)
                    });
                }
                if (this.deleteConfirm) {
                    this.deleteConfirm
                        .off('ok')
                        .on('ok', _.bind(function () {
                            InventoryLevelItemView.__super__.removeRow.apply(this, arguments);
                        }, this))
                        .open();
                }
            } else {
                if (!this.deleteForbid) {
                    this.deleteForbid = new DeleteConfirmation({
                        title: __(this.options.deleteForbiddenTitle),
                        content: __(this.options.deleteForbiddenMessage),
                        allowOk: false
                    });
                }
                if (this.deleteForbid) {
                    this.deleteForbid
                        .off('ok')
                        .open();
                }
            }
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

