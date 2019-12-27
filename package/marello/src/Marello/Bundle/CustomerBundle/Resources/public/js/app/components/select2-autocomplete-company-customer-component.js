define(function (require) {
    'use strict';
    const
        mediator = require('oroui/js/mediator'),
        Select2AutocompleteComponent = require('oro/select2-autocomplete-component');
    const Select2AutocompleteCompanyCustomerComponent = Select2AutocompleteComponent.extend({
        
        /**
         * @property {Object}
         */
        options: {
            companyDataContainer: '.marello-customer-company-select-container',
            attribute: 'company'
        },
        
        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.$sourceElement = options._sourceElement;
            this.saveData($(this.options.companyDataContainer).data(this.options.attribute));
            mediator.on('marello_customer:company:changed', this.onCompanyChange, this);
            Select2AutocompleteCompanyCustomerComponent.__super__.initialize.call(this, options);
        },
        
        makeQuery: function (query) {
            var company_id = this.getData().id;
            if (company_id !== undefined) {
                return query + ';' + company_id;
            }
            
            return query;
        },
        
        onCompanyChange: function(e) {
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

            mediator.off('marello_customer:company:changed', this.onCompanyChange, this);
            Select2AutocompleteCompanyCustomerComponent.__super__.dispose.call(this);
        }
    });
    
    return Select2AutocompleteCompanyCustomerComponent;
});
