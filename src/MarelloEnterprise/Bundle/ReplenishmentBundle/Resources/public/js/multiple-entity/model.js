define(['backbone'],
    function(Backbone) {
        'use strict';

        /**
         * @export  oroform/js/multiple-entity/model
         * @class   oroform.MultipleEntity.Model
         * @extends Backbone.Model
         */
        return Backbone.Model.extend({
            defaults: {
                id: null,
                isDefault: false,
                sku: null,
                name: null,
                manufacturingCode: null,
                status: null,
                categories: null,
                image: null,
                createdAt: null
            }
        });
    });
