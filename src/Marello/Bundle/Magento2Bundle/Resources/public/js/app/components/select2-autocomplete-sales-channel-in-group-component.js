define(function (require) {
    'use strict';

    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const Select2AutocompleteComponent = require('oro/select2-autocomplete-component');
    const logger = require('oroui/js/tools/logger');
    const messenger = require('oroui/js/messenger');
    const Select2AutocompleteSalesChannelInGroupComponent = Select2AutocompleteComponent.extend({
        /**
         * @property {jQuery}
         */
        $el: null,

        /**
         * @property {jQuery}
         */
        $salesChannelGroupEl: null,

        /**
         * @inheritDoc
         */
        constructor: function Select2AutocompleteSalesChannelInGroupComponent(options) {
            Select2AutocompleteSalesChannelInGroupComponent.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            Select2AutocompleteSalesChannelInGroupComponent.__super__.initialize.call(this, options);
            this.$el = $(options._sourceElement);
        },

        /**
         * @param {jQuery} $salesChannelGroupEl
         */
        setSalesChannelGroupEl: function($salesChannelGroupEl) {
            if (this.$salesChannelGroupEl) {
                this.$salesChannelGroupEl.off(
                    'change',
                    _.bind(this.handleSalesChannelGroupChange, this)
                );
            }

            this.$salesChannelGroupEl = $salesChannelGroupEl;
            this.$salesChannelGroupEl.on(
                'change',
                _.bind(this.handleSalesChannelGroupChange, this)
            );
        },

        /**
         * @param controlsComponent
         */
        setWebsiteToSalesChannelControlsComponent: function(controlsComponent) {
            this.controlsComponent = controlsComponent;
        },

        clearWebsiteToSalesChannelControlsComponent: function() {
            this.controlsComponent = null;
        },

        handleSalesChannelGroupChange: function() {
            this.clearData();
        },

        clearData: function() {
            this.$el.inputWidget('val', '');
        },

        isValidData: function() {
            let selectedData = this.$el.inputWidget('data');
            return !_.isEmpty(selectedData);
        },

        getData: function () {
            let selectedData = this.$el.inputWidget('data');
            if (this.isValidData()) {
                return {
                    'id' : selectedData.id,
                    'name': selectedData.name
                }
            }

            return {};
        },

        makeQuery: function (query) {
            if (null === this.controlsComponent) {
                logger.warn(
                    'Invalid configuration of autocompleteSalesChannelInGroup component. No controls component present.'
                );

                return query + ';0;';
            }

            let salesChannelGroupId = this.controlsComponent.getSalesChannelGroupId();
            if (!salesChannelGroupId) {
                messenger.notificationFlashMessage(
                    'error',
                    __('marello.magento2.mapping_form.no_sales_group_selected')
                );
            }

            let usedSalesChannelIds = this.controlsComponent.getUsedSalesChannelIds();
            return query + ';' + salesChannelGroupId + ';' + usedSalesChannelIds.join(',');
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$el.off();
            delete this.$el;

            if (this.$salesChannelGroupEl) {
                this.$salesChannelGroupEl.off(
                    'change',
                    _.bind(this.handleSalesChannelGroupChange, this)
                );
            }
            this.$salesChannelGroupEl = null;

            Select2AutocompleteSalesChannelInGroupComponent.__super__.dispose.call(this);
        }
    });

    return Select2AutocompleteSalesChannelInGroupComponent;
});
