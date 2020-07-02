define(function(require) {
    'use strict';

    const BaseModel = require('oroui/js/app/models/base/model');
    const _ = require('underscore');
    const WebsiteToSalesChannelMappingModel = BaseModel.extend({
        defaults: {
            originWebsiteId: null,
            websiteName: null,
            salesChannelId: null,
            salesChannelName: null
        },

        /**
         * @inheritDoc
         */
        constructor: function WebsiteToSalesChannelMappingModel() {
            WebsiteToSalesChannelMappingModel.__super__.constructor.apply(this, arguments);
        },

        getOriginWebsiteId: function() {
            return this.get('originWebsiteId');
        },

        getWebsiteName: function() {
            return this.get('websiteName');
        },

        getSalesChannelId: function() {
            return this.get('salesChannelId');
        },

        getSalesChannelName: function() {
            return this.get('salesChannelName');
        },

        isValid: function () {
            return !_.isNull(this.get('originWebsiteId')) && !_.isNull(this.get('websiteName')) &&
                !_.isNull(this.get('salesChannelId')) && !_.isNull(this.get('salesChannelName'));
        },

        serialize: function () {
            return {
                originWebsiteId: this.get('originWebsiteId'),
                websiteName: this.get('websiteName'),
                salesChannelId: this.get('salesChannelId'),
                salesChannelName: this.get('salesChannelName')
            };
        }
    });

    return WebsiteToSalesChannelMappingModel;
});
