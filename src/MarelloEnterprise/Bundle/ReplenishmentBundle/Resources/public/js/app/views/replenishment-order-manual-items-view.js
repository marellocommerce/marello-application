define(function(require) {
    'use strict';

    const $ = require('jquery'),
        _ = require('underscore'),
        mediator = require('oroui/js/mediator'),
        AbstractItemsView = require('marellolayout/js/app/views/abstract-items-view');

    const ReplenishmentOrderManualItemsView = AbstractItemsView.extend({
        /**
         * @property {Object}
         */
        options: {
            data: {}
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            ReplenishmentOrderManualItemsView.__super__.initialize.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        handleLayoutInit: function() {
            ReplenishmentOrderManualItemsView.__super__.handleLayoutInit.apply(this, arguments);

            mediator.on('replenishment:form-changes:trigger', this.loadingStart, this);
            mediator.on('replenishment:get:line-items-data', this.getLineItemsData, this);
            mediator.on('replenishment:form-changes:load', this.loadLineItemsData, this);
        },

        /**
         * @param {Function} callback
         */
        getLineItemsData: function(callback) {
            callback(this.options.data);
        },

        /**
         * @param {Object} response
         */
        loadLineItemsData: function(response) {
            this.loadingEnd();
            if (response === undefined || response['manualItems'] === undefined || Object.keys(response['manualItems']).length === 0) {
                return;
            }
            mediator.trigger('replenishment:refresh:line-items', response['manualItems']);
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off('replenishment:form-changes:trigger', this.loadingStart, this);
            mediator.off('replenishment:get:line-items-data', this.getLineItemsData, this);
            mediator.off('replenishment:form-changes:load', this.loadLineItemsData, this);

            ReplenishmentOrderManualItemsView.__super__.dispose.call(this);
        }
    });

    return ReplenishmentOrderManualItemsView;
});
