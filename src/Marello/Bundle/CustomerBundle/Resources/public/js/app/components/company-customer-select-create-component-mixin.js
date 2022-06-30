define(function(require) {
    'use strict';

    const
        $ = require('jquery'),
        mediator = require('oroui/js/mediator');

    const selectCreateComponentMixin = {

        options: {
            companyDataContainer: '.marello-customer-company-select-container',
            attribute: 'company'
        },
        
        _super: function() {
            throw new Error('_super() should be defined');
        },

        initialize: function(options) {
            this._super().initialize.apply(this, arguments);
            var companyData = $(this.options.companyDataContainer).data(this.options.attribute);
            if (companyData !== undefined) {
                this.saveData(companyData.id);
            }
            mediator.on('marello_customer:company:changed', this.onCompanyChange, this);
        },

        onCompanyChange: function(event) {
            if (event.to !== undefined) {
                this.saveData(event.to.id);
            }
        },

        saveData: function(companyId) {
            var parts = this.getUrlParts();
            parts.grid.parameters.params = {'companyId': companyId};
            this.setUrlParts(parts);
        },
        
        dispose: function() {
            if (this.disposed) {
                return;
            }
            
            mediator.off('marello_customer:company:changed', this.onCompanyChange, this);
            this._super().dispose.apply(this, arguments);
        }
    };

    return selectCreateComponentMixin;
});
