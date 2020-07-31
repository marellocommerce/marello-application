define(function(require) {
    'use strict';

    require('oroui/js/items-manager/table');
    const $ = require('jquery');
    const _ = require('underscore');
    const BaseComponent = require('oroui/js/app/components/base/component');
    const BaseCollection = require('oroui/js/app/models/base/collection');
    const WebsiteProvider = require('../provider/website-provider');
    const Utils = require('../utils/utils');
    const logger = require('oroui/js/tools/logger');
    const WebsiteToSalesChannelGridView = require('../views/website-to-sales-channel-grid-view');
    const WebsiteToSalesChannelMappingModel = require('../models/website-to-sales-channel-mapping-model');
    const WebsiteToSalesChannelControlsComponent = BaseComponent.extend({
        /**
         * @property {jQuery}
         */
        $el: null,

        /**
         * @property {jQuery}
         */
        $salesChannelSelectEl: null,

        /**
         * @property {jQuery}
         */
        $websiteSelectEl: null,

        /**
         * @property {jQuery}
         */
        $addBtnEl: null,

        /**
         * @property {jQuery}
         */
        $salesChannelGroupEl: null,

        /**
         * @property {jQuery}
         */
        $websiteToSalesChannelMappingEl: null,

        /**
         * @property {jQuery}
         */
        $salesChannelToWebsiteMappingTableBodyEl: null,

        /**
         * @property {jQuery}
         */
        $noDataEl: null,

        /**
         * @property {BaseComponent}
         */
        websiteSelectComponent: null,

        /**
         * @property {BaseComponent}
         */
        salesChannelSelectComponent: null,

        /**
         * @property {string}
         */
        salesChannelSelectSelector: '[data-role="sales-channel-in-group-select"]',

        /**
         * @property {string}
         */
        websiteSelectSelector: '[data-role="website-select"]',

        /**
         * @property {string}
         */
        addBtnSelector: '[data-role="add-mapping-item"]',

        /**
         * @property {string}
         */
        salesChannelToWebsiteMappingTableBodySelector: '[data-role="sales-channel-to-website-mapping-table-body"]',

        /**
         * @property {string}
         */
        noDataSelector: '.no-data',

        /**
         * @property {BaseCollection}
         */
        collection: null,

        /**
         * @property {WebsiteToSalesChannelGridView}
         */
        view: null,

        /**
         * @inheritDoc
         */
        constructor: function WebsiteToSalesChannelControlsComponent() {
            WebsiteToSalesChannelControlsComponent.__super__.constructor.apply(this, arguments);
        },

        /**
         * @param {Object} options
         */
        initialize: function(options) {
            WebsiteToSalesChannelControlsComponent.__super__.initialize.apply(this, arguments);

            this._initElements(options);
            this._initCollection();
            this._initView();

            let websiteSelectComponentPromise = options._subPromises['websiteSelectComponent'];
            this._prepareWebsiteSelectComponent(websiteSelectComponentPromise);

            let salesChannelSelectComponentPromise = options._subPromises['salesChannelInGroupSelectComponent'];
            this._prepareSalesChannelSelectComponent(salesChannelSelectComponentPromise);
        },

        /**
         * @param {Object} options
         */
        _initElements: function(options) {
            this.$el = $(options._sourceElement);
            this.$salesChannelSelectEl  = this.$el.find(this.salesChannelSelectSelector);
            this.$websiteSelectEl = this.$el.find(this.websiteSelectSelector);
            this.$addBtnEl = this.$el.find(this.addBtnSelector);
            this.$noDataEl = this.$el.find(this.noDataSelector);
            this.$salesChannelToWebsiteMappingTableBodyEl = this.$el.find(
                this.salesChannelToWebsiteMappingTableBodySelector
            );
            this.$salesChannelGroupEl = $(options.selectorSalesChannelGroup);
            this.$websiteToSalesChannelMappingEl = $(options.selectorWebsiteToSalesChannelMapping);

            let elements = {
                $addBtnEl: this.$addBtnEl,
                $salesChannelSelectEl: this.$salesChannelGroupEl,
                $websiteSelectEl: this.$websiteSelectEl,
                $salesChannelGroupEl: this.$salesChannelGroupEl,
                $noDataEl: this.$noDataEl,
                $websiteToSalesChannelMappingEl: this.$websiteToSalesChannelMappingEl,
                $salesChannelToWebsiteMappingTableBodyEl: this.$salesChannelToWebsiteMappingTableBodyEl
            };

            let invalidOptionKeys = Utils.getInvalidJqueryOptionKeys(elements, [
                '$addBtnEl',
                '$salesChannelSelectEl',
                '$websiteSelectEl',
                '$salesChannelGroupEl',
                '$noDataEl',
                '$websiteToSalesChannelMappingEl',
                '$salesChannelToWebsiteMappingTableBodyEl'
            ]);

            if (invalidOptionKeys.length) {
                // unable to initialize
                this.$el.remove();
                throw new TypeError('Missing required form element (s): ' + invalidOptionKeys.join(', '));
            }
        },

        _initCollection: function() {
            const WebsiteToSalesChannelMappingCollection = BaseCollection.extend({
                model: WebsiteToSalesChannelMappingModel
            });

            let websiteToSalesChannelMapping = JSON.parse(this.$websiteToSalesChannelMappingEl.val() || '[]');
            this.collection = new WebsiteToSalesChannelMappingCollection(websiteToSalesChannelMapping);
            this.collection.on('add remove change reset', _.bind(this.updateHiddenField, this));
        },

        _initView: function() {
            this.view = new WebsiteToSalesChannelGridView({
                el: this.$el,
                collection: this.collection,
                $addBtnEl: this.$addBtnEl,
                $noDataEl: this.$noDataEl,
                $salesChannelSelectEl: this.$salesChannelSelectEl,
                $websiteSelectEl: this.$websiteSelectEl,
                $salesChannelGroupEl: this.$salesChannelGroupEl,
                $salesChannelToWebsiteMappingTableBodyEl: this.$salesChannelToWebsiteMappingTableBodyEl,
                getSelectedMappingDataCallback: _.bind(this.getSelectedMappingData, this),
                clearMappingFieldsCallback: _.bind(this.clearMappingFields, this)
            });
        },

        /**
         * @param {Promise} websiteSelectComponentPromise
         */
        _prepareWebsiteSelectComponent: function(websiteSelectComponentPromise) {
            let self = this;
            websiteSelectComponentPromise.then(function (component) {
                component.setWebsiteToSalesChannelControlsComponent(self);
                self.websiteSelectComponent = component;
            });
        },

        /**
         * @param {Promise} salesChannelSelectComponentPromise
         */
        _prepareSalesChannelSelectComponent: function(salesChannelSelectComponentPromise) {
            let self = this;
            salesChannelSelectComponentPromise.then(function (component) {
                component.setWebsiteToSalesChannelControlsComponent(self);
                component.setSalesChannelGroupEl(self.$salesChannelGroupEl);
                self.salesChannelSelectComponent = component;
            });
        },

        /**
         * @return {WebsiteToSalesChannelMappingModel|boolean}
         */
        getSelectedMappingData: function() {
            if (!this.websiteSelectComponent || !this.salesChannelSelectComponent) {
                return null;
            }

            let websiteData = this.websiteSelectComponent.getData();
            let salesChannelData = this.salesChannelSelectComponent.getData();

            let model = new WebsiteToSalesChannelMappingModel({
                websiteOriginId: websiteData.id,
                websiteName: websiteData.name,
                salesChannelId: salesChannelData.id,
                salesChannelName: salesChannelData.name
            });

            if (model.isValid()) {
                return model;
            }

            logger.warn(
                'Got invalid data to initialize WebsiteToSalesChannelMappingModel.'
            );

            return null;
        },

        clearMappingFields: function() {
            if (!this.websiteSelectComponent || !this.salesChannelSelectComponent) {
                return null;
            }

            this.websiteSelectComponent.clearData();
            this.salesChannelSelectComponent.clearData();
        },

        updateHiddenField: function() {
            let hiddenElValue = '';
            if (!this.collection.isEmpty()) {
                hiddenElValue = JSON.stringify(this.collection);
            }
            this.$websiteToSalesChannelMappingEl.val(hiddenElValue);
        },

        getSalesChannelGroupId: function() {
            let salesChannelGroupId = this.$salesChannelGroupEl.val();
            return _.isUndefined(salesChannelGroupId) ? 0 : salesChannelGroupId;
        },

        getUsedSalesChannelIds: function() {
            return this.collection.map(function (websiteToSalesChannelMappingModel) {
                return websiteToSalesChannelMappingModel.getSalesChannelId();
            });
        },

        /**
         * @return {Object}
         */
        getAvailableWebsiteDTOsFilteredPromise: function() {
            let deferredObject = $.Deferred();
            let websiteDTOsPromise = WebsiteProvider.getWebsiteDTOsPromise();
            websiteDTOsPromise.then(_.bind(function (websiteDTOs) {
                let usedWebsiteOriginIds = this.getUsedWebsiteOriginIds();
                let filteredWebsiteDTOs = _.filter(websiteDTOs, function (websiteDTO) {
                    return -1 === _.indexOf(usedWebsiteOriginIds, websiteDTO.getId());
                });

                deferredObject.resolve(filteredWebsiteDTOs);
            }, this));

            return deferredObject.promise();
        },

        /**
         * @return {array}
         */
        getUsedWebsiteOriginIds: function() {
            return this.collection.map(function (websiteToSalesChannelMappingModel) {
                return websiteToSalesChannelMappingModel.getWebsiteOriginId();
            });
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.collection.off('add remove change reset', _.bind(this.updateHiddenField, this));

            const properties = [
                '$el',
                '$addBtnEl',
                '$salesChannelSelectEl',
                '$websiteSelectEl',
                '$noDataEl',
                '$salesChannelGroupEl',
                '$websiteToSalesChannelMappingEl',
                '$salesChannelToWebsiteMappingTableBodyEl',
                'websiteSelectComponent',
                'salesChannelSelectComponent'
            ];

            _.each(properties, _.bind(function (property) {
                delete this[property];
            }, this));

            WebsiteToSalesChannelControlsComponent.__super__.dispose.call(this);
        }
    });

    return WebsiteToSalesChannelControlsComponent;
});
