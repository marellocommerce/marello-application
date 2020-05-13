define(function(require) {
    'use strict';

    const MultipleEntityComponent = require('oroform/js/multiple-entity/component');
    const MultipleEntityModel = require('marelloenterprisereplenishment/js/multiple-entity/model');

    const ReplenishmentProductComponent = MultipleEntityComponent.extend({
        /**
         * @inheritDoc
         */
        constructor: function ReplenishmentProductComponent(options) {
            ReplenishmentProductComponent.__super__.constructor.call(this, options);
        },

        onModelSelect: function(value, model, listener) {
            const id = model.get('id');
            if (model.get(listener.columnName)) {
                this.addedModels[id] = new MultipleEntityModel({
                    id: model.get('id'),
                    isDefault: false,
                    productName: model.get('productName'),
                    sku: model.get('sku'),
                    manufacturingCode: model.get('manufacturingCode'),
                    status: model.get('status'),
                    categories: model.get('categories'),
                    image: model.get('image'),
                    createdAt: model.get('createdAt'),
                    label: this._getLabel(model),
                    extraData: this._getExtraData(model)
                });
            } else if (this.addedModels.hasOwnProperty(id)) {
                delete this.addedModels[id];
            }
        }
    });

    return ReplenishmentProductComponent;
});