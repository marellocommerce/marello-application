define(function(require) {
    'use strict';

    const BaseModel = require('oroui/js/app/models/base/model');
    const _ = require('underscore');

    const CheckConnectionModel = BaseModel.extend({
        defaults: {
            websiteDTOs: {},
            isRequiredToCheckConnection: true
        },

        /**
         * @inheritDoc
         */
        constructor: function CheckConnectionModel() {
            CheckConnectionModel.__super__.constructor.apply(this, arguments);
        },

        setConnectionChecked: function() {
            this.set('isRequiredToCheckConnection', false);
        },

        setConnectionRequiredToCheck: function() {
            this.set('isRequiredToCheckConnection', true);
        },

        isConnectionRequiredToCheck: function() {
            return this.get('isRequiredToCheckConnection');
        },

        /**
         * @param {object} websiteDTOs
         *
         * {<website_id>: WebsiteDTO, ...}
         */
        setWebsiteDTOs: function (websiteDTOs) {
            this.set('websiteDTOs', websiteDTOs);
        },

        resetWebsiteDTOsField: function() {
            this.set('websiteDTOs', this.defaults.websiteDTOs);
        },

        hasWebsites: function() {
            return _.keys(this.get('websiteDTOs')).length > 1;
        },

        /**
         * @return {object}
         */
        getWebsiteDTOs: function () {
            return this.get('websiteDTOs');
        }
    });

    return CheckConnectionModel;
});
