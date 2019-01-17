define(function(require) {
    'use strict';

    var InventoryLevelItemView,
        _ = require('underscore'),
        __ = require('orotranslation/js/translator'),
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
            deleteMessage: 'marelloenterprise.inventory.inventoryitem.inventory_levels.delete.confirmation'
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
            if (this.confirm) {
                this.confirm
                    .off()
                    .remove();
            }

            InventoryLevelItemView.__super__.dispose.call(this);
        }

    });

    return InventoryLevelItemView;
});

