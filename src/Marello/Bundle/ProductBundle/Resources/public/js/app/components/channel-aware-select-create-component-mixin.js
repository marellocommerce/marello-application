define(function(require) {
    'use strict';

    var selectCreateComponentMixin,
        $ = require('jquery'),
        mediator = require('oroui/js/mediator');

    selectCreateComponentMixin = {

        options: {
            salesChannelDataContainer: '.marello-sales-channel-select-container',
            attribute: 'salesChannel'
        },
        
        _super: function() {
            throw new Error('_super() should be defined');
        },

        initialize: function(options) {
            this._super().initialize.apply(this, arguments);
            var channelData = $(this.options.salesChannelDataContainer).data(this.options.attribute);
            if (channelData !== undefined) {
                this.saveData(channelData.id);
            }
            mediator.on('marello_sales:channel:changed', this.onSalesChannelChange, this);
        },

        onSalesChannelChange: function(event) {
            if (event.to !== undefined) {
                this.saveData(event.to.id);
            }
        },

        saveData: function(channelId) {
            var parts = this.getUrlParts();
            parts.grid.parameters.params = {'channelId': channelId};
            this.setUrlParts(parts);
            
        },
        
        dispose: function() {
            if (this.disposed) {
                return;
            }
            
            mediator.off('marello_sales:channel:changed', this.onSalesChannelChange, this);
            this._super().dispose.apply(this, arguments);
        }
    };

    return selectCreateComponentMixin;
});
