define(function(require) {
    'use strict';

    const BaseModel = require('oroui/js/app/models/base/model');
    const _ = require('underscore');
    const WebsiteToSalesChannelMappingModel = BaseModel.extend({
        defaults: {
            websiteOriginId: null,
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

        getWebsiteOriginId: function() {
            return this.get('websiteOriginId');
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
            return !_.isNull(this.get('websiteOriginId')) && !_.isNull(this.get('websiteName')) &&
                !_.isNull(this.get('salesChannelId')) && !_.isNull(this.get('salesChannelName'));
        },

        serialize: function () {
            return {
                websiteOriginId: this.get('websiteOriginId'),
                websiteName: this.get('websiteName'),
                salesChannelId: this.get('salesChannelId'),
                salesChannelName: this.get('salesChannelName')
            };
        }
    });

    return WebsiteToSalesChannelMappingModel;
});
