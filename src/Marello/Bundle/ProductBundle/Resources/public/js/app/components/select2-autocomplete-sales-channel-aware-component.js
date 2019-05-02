define(function (require) {
    'use strict';
    var Select2AutocompleteChannelAwareComponent,
        mediator = require('oroui/js/mediator'),
        Select2AutocompleteComponent = require('oro/select2-autocomplete-component');
    Select2AutocompleteChannelAwareComponent = Select2AutocompleteComponent.extend({
        
        /**
         * @property {Object}
         */
        options: {
            salesChannelDataContainer: '.marello-sales-channel-select-container',
            attribute: 'salesChannel'
        },
        
        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.$sourceElement = options._sourceElement;
            this.saveData($(this.options.salesChannelDataContainer).data(this.options.attribute));
            mediator.on('marello_sales:channel:changed', this.onSalesChannelChange, this);
            Select2AutocompleteChannelAwareComponent.__super__.initialize.call(this, options);
        },
        
        makeQuery: function (query) {
            var channel_id = this.getData().id;
            return query + ';' + channel_id;
        },
        
        onSalesChannelChange: function(e) {
            if (e.to !== undefined) {
                this.saveData(e.to);
            }
        },
        
        /**
         * Return units from data attribute
         *
         * @returns {jQuery.Element}
         */
        getData: function() {
            return this.$sourceElement.data(this.options.attribute) || {};
        },

        /**
         * Save data to data attribute
         *
         * @param {Object} data
         */
        saveData: function(data) {
            this.$sourceElement.data(this.options.attribute, data);
        },
        
        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off('marello_sales:channel:changed', this.onSalesChannelChange, this);
            Select2AutocompleteChannelAwareComponent.__super__.dispose.call(this);
        }
    });
    
    return Select2AutocompleteChannelAwareComponent;
});
