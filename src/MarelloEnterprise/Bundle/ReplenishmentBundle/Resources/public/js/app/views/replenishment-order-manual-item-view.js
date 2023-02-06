define(function(require) {
    'use strict';

    const $ = require('jquery'),
        _ = require('underscore'),
        mediator = require('oroui/js/mediator'),
        AbstractItemView = require('marellolayout/js/app/views/abstract-item-view');

    const ReplenishmentOrderManualItemView = AbstractItemView.extend({
        /**
         * @property {Object}
         */
        options: {
            ftid: '',
        },

        /**
         * @property {Object}
         */
        data: {},

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            ReplenishmentOrderManualItemView.__super__.initialize.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        handleLayoutInit: function() {
            ReplenishmentOrderManualItemView.__super__.handleLayoutInit.apply(this, arguments);
            this.toggleQuantityField();

            this.addFieldEvents('product', this.updateOrderItemData);
            this.addFieldEvents('origin', this.updateOrderItemData);
            this.addFieldEvents('allQuantity', this.toggleQuantityField);
            mediator.trigger('replenishment:get:line-items-data', _.bind(this.setOrderItemData, this));
            mediator.on('replenishment:refresh:line-items', this.setOrderItemData, this);
        },

        updateOrderItemData: function() {
            let productId = this.getProductId();
            if (productId.length === 0) {
                this.setOrderItemData({});
                return;
            }

            // Validate is the current product+origin pair already exist
            let originId = this.getOriginId();
            let stop = false;
            if (originId.length !== 0) {
                let self = this;
                let $collection = this.$el.closest('[data-name="field__manual-items"]')
                $collection.find('[data-name="' + this.fieldsByName.product.data('name') + '"]').each(function() {
                    let $field = $(this);
                    if ($field.prop('id') === self.fieldsByName.product.prop('id')) {
                        return;
                    }

                    if ($field.val() === productId) {
                        let $originField = $field.closest('tr').find('[data-name="' + self.fieldsByName.origin.data('name') + '"]');
                        if ($originField.val().length !== 0 && $originField.val() === originId) {
                            self.addFieldError(self.fieldsByName.origin, _.__('marelloenterprise.replenishment.replenishmentorderconfig.form.validation.origin.already_selected'));
                            stop = true;
                        }
                    }
                });
            }

            if (stop) {
                return;
            }
            mediator.trigger('replenishment:form-changes:trigger', {updateFields: ['manualItems']});
        },

        /**
         * @param $field {jQuery}
         * @param errorMessage {String}
         */
        addFieldError: function($field, errorMessage) {
            // TODO remove error after change + prevent submit
            // see container.find('form')).data('validator')
            let $errorContainer = $field.siblings('.validation-failed');
            if (!$errorContainer.length) {
                $errorContainer = $('<span class="validation-failed"></span>');
                $field.after($errorContainer);
            }

            $errorContainer.show().text(errorMessage);
            $field.addClass('error');
        },

        toggleQuantityField: function() {
            if (this.fieldsByName.allQuantity.is(':checked')) {
                this.fieldsByName.quantity.val('');
                this.fieldsByName.quantity.prop('disabled', true);
            } else {
                this.fieldsByName.quantity.prop('disabled', false);
            }
        },

        /**
         * @param data {Object}
         */
        setOrderItemData: function(data) {
            if (data === undefined || typeof(data) == 'undefined' || Object.keys(data).length === 0) {
                return;
            }

            let identifier = this._getItemIdentifier();
            if (identifier && data[identifier] !== undefined) {
                if(data[identifier].message !== undefined) {
                    this.data = {};
                } else {
                    this.data = data[identifier] || {};
                }

            } else {
                this.data = {};
            }

            this.fieldsByName.unit.val(this.getProductUnitFromData());
            this.fieldsByName.availableQuantity.val(this.getAvailableQuantityFromData());
        },

        /**
         * @returns {String}
         */
        getOriginId: function() {
            return this.fieldsByName.hasOwnProperty('origin') ? this.fieldsByName.origin.val() : '';
        },

        /**
         * @returns {String|Null}
         */
        getProductUnitFromData: function() {
            return !_.isEmpty(this.data) ? this.data.unit : null;
        },

        /**
         * @returns {Number|Null}
         */
        getAvailableQuantityFromData: function() {
            return !_.isEmpty(this.data) ? this.data.availableQuantity : null;
        },

        /**
         * @returns {String|Null}
         * @private
         */
        _getItemIdentifier: function() {
            let rowItemIdentifier = this.getRowItemIdentifier();
            if (rowItemIdentifier.length === 0) {
                return null;
            }

            return 'item-id-' + rowItemIdentifier;
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off('replenishment:refresh:line-items', this.setOrderItemData, this);

            ReplenishmentOrderManualItemView.__super__.dispose.call(this);
        }
    });

    return ReplenishmentOrderManualItemView;
});
