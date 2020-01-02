define(function(require) {
    'use strict';

    const MultipleEntityComponent = require('oroform/js/multiple-entity/component');
    const MultipleEntityModel = require('marellopurchaseorder/js/multiple-entity/model');

    const PurchaseOrderComponent = MultipleEntityComponent.extend({
        optionNames: MultipleEntityComponent.prototype.optionNames.concat([
            'currency'
        ]),

        /**
         * @inheritDoc
         */
        constructor: function PurchaseOrderComponent(options) {
            PurchaseOrderComponent.__super__.constructor.call(this, options);
        },

        onModelSelect: function(value, model, listener) {
            const id = model.get('id');
            if (model.get(listener.columnName)) {
                this.addedModels[id] = new MultipleEntityModel({
                    'id': model.get('id'),
                    'label': 'product',
                    'productName': model.get('productName'),
                    'value': model.get('sku') + ' - ' + model.get('productName'),
                    'sku': model.get('sku'),
                    'orderAmount': model.get('orderAmount'),
                    'purchasePrice': model.get('purchasePrice'),
                    'currency': this.currency,
                    isDefault: false
                });
            } else if (this.addedModels.hasOwnProperty(id)) {
                delete this.addedModels[id];
            }
        }
    });

    return PurchaseOrderComponent;
});