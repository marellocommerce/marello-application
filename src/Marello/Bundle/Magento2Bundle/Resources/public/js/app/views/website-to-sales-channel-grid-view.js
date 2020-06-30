define(function(require) {
    'use strict';

    require('oroui/js/items-manager/table');
    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const BaseView = require('oroui/js/app/views/base/view');
    const mappingItemTemplate = require('text-loader!./templates/mapping-item.html');
    const WebsiteToSalesChannelGridView = BaseView.extend({
        /**
         * @type {function(Object)}
         */
        itemTemplate: _.template(mappingItemTemplate),

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
        $noDataEl: null,

        /**
         * @property {jQuery}
         */
        $salesChannelGroupEl: null,

        /**
         * @property {jQuery}
         */
        $salesChannelToWebsiteMappingTableBodyEl: null,

        /**
         * @property {CallableFunction}
         */
        getSelectedMappingDataCallback: null,

        /**
         * @property {CallableFunction}
         */
        clearMappingFieldsCallback: null,

        /**
         * @inheritDoc
         */
        constructor: function WebsiteToSalesChannelGridView() {
            WebsiteToSalesChannelGridView.__super__.constructor.apply(this, arguments);
        },

        initialize: function(options) {
            _.extend(this, _.pick(
                options,
                [
                    '$salesChannelSelectEl',
                    '$websiteSelectEl',
                    '$addBtnEl',
                    '$noDataEl',
                    '$salesChannelGroupEl',
                    '$salesChannelToWebsiteMappingTableBodyEl',
                    'getSelectedMappingDataCallback',
                    'clearMappingFieldsCallback'
                ]
            ));

            this.initTable();
            this.initEventListeners();
            this.updateVisibleStateOfTableMappingDataEl();
            this.collection.on('add remove change reset', _.bind(this.onCollectionChange, this));
        },

        initTable: function() {
            this.$salesChannelToWebsiteMappingTableBodyEl.itemsManagerTable({
                collection: this.collection,
                itemTemplate: this.itemTemplate,
                sorting: false,
                itemRender: function itemRender(template, data) {
                    let context = _.extend({__: __}, data);

                    return template(context);
                },
                deleteHandler: _.partial(function(collection, model, data) {
                    collection.remove(model);
                }, this.collection)
            });
        },

        initEventListeners: function() {
            this.$addBtnEl.on('click', _.bind(this.onBtnAddClickHandler, this));
            this.$salesChannelGroupEl.on('change', _.bind(this.onSalesChannelGroupChange, this))
        },

        onSalesChannelGroupChange: function() {
            this.collection.reset([]);
        },

        onBtnAddClickHandler: function (e) {
            e.preventDefault();
            this.validateMappingForm();

            if (this.isValidMappingForm()) {
                let model = this.getSelectedMappingDataCallback();
                if (!model) {
                    throw new Error('Invalid model data given for valid form !');
                }

                this.collection.add(model);
                this.clearMappingFieldsCallback();
            }
        },

        validateMappingForm: function() {
            this.$salesChannelSelectEl
                .parents('.controls')
                .toggleClass('validation-error', !this.$salesChannelSelectEl.val());

            this.$websiteSelectEl
                .parents('.controls')
                .toggleClass('validation-error', !this.$websiteSelectEl.val());
        },

        isValidMappingForm: function() {
            return this.$salesChannelSelectEl.val() && this.$websiteSelectEl.val();
        },

        onCollectionChange: function() {
            this.updateVisibleStateOfTableMappingDataEl();
        },

        updateVisibleStateOfTableMappingDataEl: function() {
            if (this.collection.isEmpty()) {
                this.$salesChannelToWebsiteMappingTableBodyEl.hide();
                this.$noDataEl.show();
            } else {
                this.$noDataEl.hide();
                this.$salesChannelToWebsiteMappingTableBodyEl.show();
            }
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$addBtnEl.off('click', _.bind(this.onBtnAddClickHandler, this));
            this.$salesChannelGroupEl.off('change', _.bind(this.onSalesChannelGroupChange, this))

            const properties = [
                '$addBtnEl',
                '$salesChannelSelectEl',
                '$websiteSelectEl',
                '$noDataEl',
                '$salesChannelToWebsiteMappingTableBodyEl',
            ];

            _.each(properties, _.bind(function (property) {
                delete this[property];
            }, this));

            WebsiteToSalesChannelGridView.__super__.dispose.call(this);
        }
    });

    return WebsiteToSalesChannelGridView;
});
