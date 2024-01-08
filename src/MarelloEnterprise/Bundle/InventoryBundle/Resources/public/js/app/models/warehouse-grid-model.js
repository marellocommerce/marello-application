define(function(require) {
    'use strict';

    const BaseModel = require('oroui/js/app/models/base/model');
    const _ = require('underscore');

    /**
     * @export  marelloenterpriseinventory/js/app/models/warehouse-grid-model
     */
    const WarehouseGridModel = BaseModel.extend({
        defaultValidationResult: {
            isValid: true,
            validationCode: null
        },

        /**
         * @inheritdoc
         */
        constructor: function WarehouseGridModel(...args) {
            WarehouseGridModel.__super__.constructor.apply(this, args);
        },

        defaults: {
            id: '',
            code: '',
            name: '',
            isConsolidationWarehouse: false,
            isOrderOnDemandLocation: false,
            sortOrderOodLoc: 0,
            onlyAdded: false
        },

        getConsolidationWarehouseData: function() {
            const data = [];
            data[this.get('code')] = this.get('isConsolidationWarehouse');
            return data;
        },

        getOrderOnDemandLocationData: function() {
            const data = [];
            data[this.get('code')] = this.get('isOrderOnDemandLocation');
            return data;
        },

        getsortOrderOodLocData: function() {
            const data = [];
            data[this.get('code')] = this.get('sortOrderOodLoc');
            return data;
        },

        validateAttribute: function(attrName) {
            return this.validateValue(this.get(attrName));
        },
    });

    return WarehouseGridModel;
});
