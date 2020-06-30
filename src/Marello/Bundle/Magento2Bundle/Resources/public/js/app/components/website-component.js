define(function (require) {
    'use strict';

    require('jquery.select2');
    const $ = require('jquery');
    const _ = require('underscore');
    const BaseComponent = require('oroui/js/app/components/base/component');
    const logger = require('oroui/js/tools/logger');
    const WebsiteComponent = BaseComponent.extend({
        /**
         * @property {jQuery}
         */
        $el: null,

        /**
         * @property {BaseComponent}
         */
        controlsComponent: null,

        /**
         * @inheritDoc
         */
        constructor: function WebsiteComponent() {
            WebsiteComponent.__super__.constructor.apply(this, arguments);
        },

        /**
         * @param {Object} options
         */
        initialize: function (options) {
            WebsiteComponent.__super__.initialize.apply(this, arguments);

            this.$el = $(options._sourceElement);
            let configs = options.configs || {};
            let self = this;
            this.$el.select2(
                _.extend(configs, {
                    query: function (query) {
                        if (null === this.controlsComponent) {
                            logger.warn(
                                'Invalid configuration of website component. No controls component present.'
                            );

                            query.callback({results: []});
                            return;
                        }

                        let availableWebsiteDTOsPromise = self
                            .controlsComponent
                            .getAvailableWebsiteDTOsFilteredPromise();

                        availableWebsiteDTOsPromise.then(function (availableWebsiteDTOs) {
                            let resultWebsiteDTOs = availableWebsiteDTOs;
                            if(query.term) {
                                resultWebsiteDTOs = _.filter(availableWebsiteDTOs, function (websiteDTO) {
                                    return self.matchWebsiteByTerm(query.term, websiteDTO.getName());
                                });
                            }

                            let data = {
                                results: _.map(resultWebsiteDTOs, function (websiteDTO) {
                                    return {id: websiteDTO.getId(), text: websiteDTO.getName()};
                                })
                            };

                            query.callback(data);
                        });
                    }
                })
            );
        },

        /**
         * @param {string} term
         * @param {string} websiteName
         * @return {boolean}
         */
        matchWebsiteByTerm: function(term, websiteName) {
            let match = websiteName.toUpperCase().indexOf(term.toUpperCase());

            return match >= 0;
        },

        isValidData: function() {
            let selectedData = this.$el.select2('data');
            return !_.isEmpty(selectedData);
        },

        getData: function () {
            let selectedData = this.$el.select2('data');
            if (this.isValidData()) {
                return {
                    'id' : selectedData.id,
                    'name': selectedData.text
                }
            }

            return {};
        },

        clearData: function() {
            this.$el.select2('val', '');
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

        getSelect2Data: function() {
            if (null === this.controlsComponent) {
                logger.warn(
                    'Invalid configuration of website component. No controls component present.'
                );

                return {results: []};
            }

            let availableWebsiteDTOs = this.controlsComponent.getAvailableWebsiteDTOs();
            return {
                results: _.map(availableWebsiteDTOs, function (websiteDTO) {
                    return {id: websiteDTO.getId(), text: websiteDTO.getName()};
                })
            };
        },

        dispose: function () {
            if (this.disposed) {
                return;
            }

            this.clearWebsiteToSalesChannelControlsComponent();
            this.$el.off();
            delete this.$el;

            WebsiteComponent.__super__.dispose.call(this);
        }
    });

    return WebsiteComponent;
});
