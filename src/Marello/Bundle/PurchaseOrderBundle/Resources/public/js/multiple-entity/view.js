define(function(require) {
    'use strict';

    const _ = require('underscore');
    const Backbone = require('backbone');
    const DialogWidget = require('oro/dialog-widget');

    /**
     * @export  oroform/js/multiple-entity/view
     * @class   oroform.MultipleEntity.View
     * @extends Backbone.View
     */
    const EntityView = Backbone.View.extend({
        tagName: "tr",
        className: "purchase-order-line-item display-values marello-line-item",

        events: {
            'click .remove-btn': 'removeElement',
            'change .default-selector': 'defaultSelected'
        },

        options: {
            name: null,
            hasDefault: false,
            defaultRequired: false,
            model: null,
            template: null
        },

        /**
         * @inheritDoc
         */
        constructor: function EntityView(options) {
            EntityView.__super__.constructor.call(this, options);
        },

        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            if (typeof this.options.template === 'string') {
                this.template = _.template(this.options.template);
            } else {
                this.template = this.options.template;
            }
            this.listenTo(this.model, 'destroy', this.remove);
            if (this.options.defaultRequired) {
                this.listenTo(this.model, 'change:isDefault', this.toggleDefault);
            }
        },

        /**
         * Display information about selected entity.
         *
         * @param {jQuery.Event} e
         */
        viewDetails: function(e) {
            e.stopImmediatePropagation();
            e.preventDefault();
            let widget = new DialogWidget({
                'url': this.options.model.get('link'),
                'title': this.options.model.get('label'),
                dialogOptions: {
                    'allowMinimize': true,
                    'width': 675,
                    'autoResize': true
                }
            });
            widget.render();
        },

        removeElement: function() {
            this.trigger('removal', this.model);
            this.model.set('id', null);
            this.model.destroy();
        },

        defaultSelected: function(e) {
            this.options.model.set('isDefault', e.target.checked);
        },

        toggleDefault: function() {
            if (this.options.defaultRequired) {
                this.$el.find('.remove-btn')[0].disabled = this.model.get('isDefault');
            }
        },

        render: function() {
            let data = this.model.toJSON();
            data.purchasePrice = parseFloat(data.purchasePrice).toFixed(2);
            this.$el.append(this.template(data));
            this.$el.find('a.entity-info').click(_.bind(this.viewDetails, this));
            this.$el.find('td.purchase-order-line-item-ordered-amount').find('input').change(_.bind(this.updateRowTotal, this));
            this.$el.find('td.purchase-order-line-item-purchase-price').find('input').change(_.bind(this.updateRowTotal, this));
            this.toggleDefault();
            this.updateRowTotal();
            return this;
        },

        updateRowTotal: function() {
            let amount = this.$el.find('td.purchase-order-line-item-ordered-amount').find('input').val();
            let price = this.$el.find('td.purchase-order-line-item-purchase-price').find('input[name*="value"]').val();
            this.model.set('orderAmount', amount);
            this.model.set('purchasePrice', price);

            let rowTotal = parseFloat(amount) * parseFloat(price);
            if (!isNaN(rowTotal)) {
                let currencySymbol = this.model.get('currency');
                this.$el.find('td.purchase-order-line-item-row-total').html(currencySymbol + rowTotal.toFixed(2));
            } else {
                this.$el.find('td.purchase-order-line-item-row-total').html('');
            }
        }
    });

    return EntityView;
});
