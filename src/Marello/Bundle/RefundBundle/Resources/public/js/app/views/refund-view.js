define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const mediator = require('oroui/js/mediator');
    const AbstractItemView = require('marellolayout/js/app/views/abstract-item-view');

    /**
     * @export marellorefund/js/app/views/refund-view
     * @extends marellolayout.app.views.AbstractItemView
     * @class marellorefund.app.views.RefundItemView
     */
    const RefundItemView = AbstractItemView.extend({
        options: {},

        /**
         * @property {Object}
         */
        data: {},

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            RefundItemView.__super__.initialize.apply(this, arguments);
        },

        /**
         * Doing something after loading child components
         */
        handleLayoutInit: function() {
            RefundItemView.__super__.handleLayoutInit.apply(this, arguments);
            this.initRefundItem();
        },

        /**
         * initialize item triggers and field events
         */
        initRefundItem: function() {
            if (this.hasProperty('quantity')) {
                this.addFieldEvents('quantity', this.updateRefundRowTotal);
            }
            if (this.hasProperty('refundAmount')) {
                this.addFieldEvents('refundAmount', this.updateRefundData);
            }

            if (this.hasProperty('taxCode')) {
                this.addFieldEvents('taxCode', this.updateRefundData);
            }

            this.updateRefundRowTotal();
        },

        hasProperty: function(property) {
            return !!this.fieldsByName.hasOwnProperty(property);
        },

        /**
         * Trigger total calculation update
         */
        updateRefundRowTotal: function() {
            mediator.trigger('refund:item-data:trigger', {updateFields: ['items', 'balance_totals', 'totals']});
            mediator.trigger('refund:form-changes:trigger', {updateFields: ['items', 'balance_totals', 'totals']});
        },

        /**
         * Trigger total calculation update
         */
        updateRefundData: function() {
            mediator.trigger('refund:item-data:trigger', {updateFields: ['items', 'balance_totals', 'totals']});
            mediator.trigger('refund:form-changes:trigger', {updateFields: ['items', 'balance_totals', 'totals']});
        },

        /**
         * @inheritDoc
         */
        removeRow: function() {
            RefundItemView.__super__.removeRow.call(this);
            mediator.trigger('refund:item-data:trigger', {updateFields: ['items', 'balance_totals', 'totals']});
            mediator.trigger('refund:form-changes:trigger', {updateFields: ['items', 'balance_totals', 'totals']});
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            RefundItemView.__super__.dispose.call(this);
        }
    });

    return RefundItemView;
});
