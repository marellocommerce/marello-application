define(function(require) {
    'use strict';

    const _ = require('underscore');
    const logger = require('oroui/js/tools/logger');
    const $ = require('jquery');
    const WebsiteProvider = {
        /**
         * @property {object}
         */
        subProviderComponent: null,

        /**
         * @param {object} subProviderComponent
         * @return {*|boolean}
         */
        isValidSubProviderComponent: function(subProviderComponent) {
            if (!_.isObject(subProviderComponent)) {
                return false;
            }

            return _.isFunction(subProviderComponent.getWebsiteDTOsPromise);
        },

        setSubProvider: function (subProviderComponent) {
            if (!this.isValidSubProviderComponent(subProviderComponent)) {
                logger.warn(
                    'Invalid website sub-provider component given!',
                    {websiteSubProvider: subProviderComponent}
                );
            }

            this.subProviderComponent = subProviderComponent;
        },

        cleanSubProvider: function() {
            this.subProviderComponent = null;
        },

        /**
         * @return {Promise}
         */
        getWebsiteDTOsPromise: function () {
            let deferredObject = $.Deferred();
            if (!this.isValidSubProviderComponent(this.subProviderComponent)) {
                logger.warn(
                    'Invalid website sub-provider component found!',
                    {websiteSubProvider: this.subProviderComponent}
                );

                deferredObject.resolve({});
                return deferredObject.promise();
            }

            return this.subProviderComponent.getWebsiteDTOsPromise();
        }
    };

    return WebsiteProvider;
});
