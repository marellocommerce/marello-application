define(function(require) {
    'use strict';

    var AbstractItemView,
        $ = require('jquery'),
        _ = require('underscore'),
        BaseView = require('oroui/js/app/views/base/view');

    /**
     * @export marellolayout/js/app/views/abstract-item-view
     * @extends oroui.app.views.base.View
     * @class marellolayout.app.views.AbstractItemView
     */
    AbstractItemView = BaseView.extend({
        options: {
            ftid: '',
        },

        /**
         * @property {jQuery}
         */
        $fields: null,

        /**
         * @property {Object}
         */
        fieldsByName: null,

        /**
         * @property {Object}
         */
        change: {},

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            if (!this.options.ftid) {
                this.options.ftid = this.$el.data('content').toString()
                    .replace(/[^a-zA-Z0-9]+/g, '_').replace(/_+$/, '');
            }

            this.initLayout().done(_.bind(this.handleLayoutInit, this));
            this.delegate('click', '.marello-remove-line-item', this.removeRow);
        },

        /**
         * Doing something after loading child components
         */
        handleLayoutInit: function() {
            var self = this;
            this.$fields = this.$el.find(':input[data-ftid]');
            this.fieldsByName = {};
            this.$fields.each(function() {
                var $field = $(this);
                var name = self.normalizeName($field.data('ftid').replace(self.options.ftid + '_', ''));
                self.fieldsByName[name] = $field;
            });
        },

        /**
         * @returns {String}
         * @private
         */
        _getProductId: function() {
            return this.fieldsByName.hasOwnProperty('product') ? this.fieldsByName.product.val() : '';
        },

        /**
         * @returns {String|Null}
         */
        getPriceValue: function() {
            return !_.isEmpty(this.price) ? this.price.value : null;
        },

        /**
         * @param {String} field
         * @param {Function} callback
         */
        addFieldEvents: function(field, callback) {
            this.fieldsByName[field].change(_.bind(function() {
                if (this.change[field]) {
                    clearTimeout(this.change[field]);
                }

                callback.call(this);
            }, this));

            this.fieldsByName[field].keyup(_.bind(function() {
                if (this.change[field]) {
                    clearTimeout(this.change[field]);
                }

                this.change[field] = setTimeout(_.bind(callback, this), 1500);
            }, this));
        },

        /**
         * Convert name with "_" to name with upper case, example: some_name > someName
         *
         * @param {String} name
         *
         * @returns {String}
         */
        normalizeName: function(name) {
            name = name.split('_');
            for (var i = 1, iMax = name.length; i < iMax; i++) {
                name[i] = name[i][0].toUpperCase() + name[i].substr(1);
            }
            return name.join('');
        },

        /**
         * remove single line item
         */
        removeRow: function() {
            this.$el.trigger('content:remove');
            this.remove();
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            AbstractItemView.__super__.dispose.call(this);
        }
    });

    return AbstractItemView;
});
