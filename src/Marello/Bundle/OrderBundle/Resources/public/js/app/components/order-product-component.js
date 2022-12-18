define(function(require) {
    'use strict';

    const MultipleEntityComponent = require('oroform/js/multiple-entity/component');
    const MultipleEntityModel = require('marellopurchaseorder/js/multiple-entity/model');

    const OrderProductComponent = MultipleEntityComponent.extend({
        optionNames: MultipleEntityComponent.prototype.optionNames.concat([
            'currency'
        ]),

        /**
         * @inheritDoc
         */
        constructor: function OrderComponent(options) {
            OrderComponent.__super__.constructor.call(this, options);
        },

        onModelSelect: function(value, model, listener) {
            const id = model.get('id');
            if (model.get(listener.columnName)) {
                this.addedModels[id] = new MultipleEntityModel({
                    'id': model.get('id')
                });
            } else if (this.addedModels.hasOwnProperty(id)) {
                delete this.addedModels[id];
            }
        }
    });

    return OrderProductComponent;
});