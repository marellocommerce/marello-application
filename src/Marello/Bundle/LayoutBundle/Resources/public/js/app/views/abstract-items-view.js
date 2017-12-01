define(function(require) {
    'use strict';

    var AbstractItemsView,
        $ = require('jquery'),
        LoadingMaskView = require('oroui/js/app/views/loading-mask-view'),
        BaseView = require('oroui/js/app/views/base/view');

    /**
     * @export marellolayout/js/app/views/abstract-items-view
     * @extends oroui.app.views.base.View
     * @class marellolayout.app.views.AbstractItemsView
     */
    AbstractItemsView = BaseView.extend({
        /**
         * @property {Object}
         */
        options: {},
        /**
         * @property {jQuery}
         */
        $form: null,

        /**
         * @property {LoadingMaskView}
         */
        loadingMaskView: null,

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            this.loadingMaskView = new LoadingMaskView({container: this.$el});
            this.initLayout().done(_.bind(this.handleLayoutInit, this));
            this.delegate('click', '.marello-add-line-item', this.addRow);
        },

        /**
         * Doing something after loading child components
         */
        handleLayoutInit: function() {
            this.$form = this.$el.closest('form');
            this.$el.find('.marello-add-line-item').mousedown(function(e) {
                $(this).click();
            });
        },

        /**
         * Show loading view
         */
        loadingStart: function() {
            this.loadingMaskView.show();
        },

        /**
         * Hide loading view
         */
        loadingEnd: function() {
            this.loadingMaskView.hide();
        },

        /**
         * handle index and html for the collection container
         * @param $listContainer
         * @returns {{nextIndex: *, nextItemHtml: *}}
         */
        getCollectionInfo: function($listContainer) {
            var index = $listContainer.data('last-index') || $listContainer.children().length;

            var prototypeName = $listContainer.attr('data-prototype-name') || '__name__';
            var html = $listContainer.attr('data-prototype').replace(new RegExp(prototypeName, 'g'), index);
            return {
                nextIndex: index,
                nextItemHtml: html
            };
        },

        /**
         * handle add button
         */
        addRow: function() {
            var _self = this.$el.find('.marello-add-line-item');
            var containerSelector = $(_self).data('container') || '.collection-fields-list';
            var $listContainer = this.$el.find('.row-oro').find(containerSelector).first();
            var collectionInfo = this.getCollectionInfo($listContainer);
            $listContainer.append(collectionInfo.nextItemHtml)
                .trigger('content:changed')
                .data('last-index', collectionInfo.nextIndex + 1);

            $listContainer.find('input.position-input').each(function(i, el) {
                $(el).val(i);
            });
        },

        /**
         * @returns {Array} products
         */
        getProductsId: function() {
            var products = this.$el.find('input[data-ftid$="_product"]');
            products = _.filter(products, function(product) {
                return product.value.length > 0;
            });
            products = _.map(products, function(product) {
                return product.value;
            });
            return products;
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            AbstractItemsView.__super__.dispose.call(this);
        }
    });

    return AbstractItemsView;
});
