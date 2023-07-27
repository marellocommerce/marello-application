define(function(require) {
    'use strict';

    const $ = require('jquery'),
        mediator = require('oroui/js/mediator'),
        _ = require('underscore'),
        BaseOrderItemsView = require('marelloorder/js/app/views/order-items-view');
    /**
     * @export marelloenterpriseorder/js/app/views/order-items-view
     * @extends marelloorder.app.views.OrderItemsView
     * @class marelloenterpriseorder.app.views.OrderItemsView
     */
    const OrderItemsView = BaseOrderItemsView.extend({
        options: {
            consolidationEnabledSelector: 'select[data-name="field__consolidation-enabled"]'
        },
        /**
         * @property {jQuery}
         */
        $form: null,

        /**
         * @property {jQuery}
         */
        $salesChannel: null,
        /**
         * @property {jQuery}
         */
        $consolidationEnabled: null,

        /**
         * @inheritdoc
         */
        constructor: function OrderItemsView(options) {
            OrderItemsView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            OrderItemsView.__super__.initialize.call(this, options);
            this.options = $.extend(true, {}, this.options, options || {});
        },

        /**
         * Doing something after loading child components
         */
        handleLayoutInit: function() {
            OrderItemsView.__super__.handleLayoutInit.call(this);
            this.$consolidationEnabled = this.$form.find(this.options.consolidationEnabledSelector);
            mediator.on('order:form-changes:load', this.updateConsolidationEnabled, this);
        },

        /**
         * trigger additional data changes when the form is loaded
         */
        initLineItemAdditionalData: function() {
            OrderItemsView.__super__.initLineItemAdditionalData.call(this);

            if (this._getConsolidationEnabled() !== null && this._getSalesChannel().length !== 0) {
                // trigger additional update for setting the On Demand setting if it's available
                mediator.trigger('order:form-changes:trigger', {updateFields: ['consolidation_enabled']});
            }
        },

        /**
         * update consolidation enabled field to reflect correct setting from
         * Saleschannel or config
         * @param response
         */
        updateConsolidationEnabled: function(response) {
            if (response === undefined || response['consolidation_enabled'] === undefined || response['consolidation_enabled'].length == 0) {
                return;
            }
            // remove old selected option first
            this._getConsolidationEnabled()
                .find('option:selected')
                .removeAttr('selected');
            // select selected option
            this._getConsolidationEnabled()
                .find('option[value="' + response['consolidation_enabled'] + '"]')
                .attr('selected','selected')
                .change();
        },

        /**
         * get Consolidation Enabled value
         * @returns {object|null}
         * @protected
         */
        _getConsolidationEnabled: function() {
            return this.$consolidationEnabled !== null ? this.$consolidationEnabled : null;
        }
    });

    return OrderItemsView;
});
