define(function(require) {
    'use strict';

    const BaseComponent = require('oroui/js/app/components/base/component');
    const widgetManager = require('oroui/js/widget-manager');
    const Address = require('marelloaddress/js/address');
    const routing = require('routing');
    const _ = require('underscore');

    const AddressWidgetComponent = BaseComponent.extend({
        optionNames: BaseComponent.prototype.optionNames.concat([
            'wid', 'addressCreateUrl', 'addressUpdateRoute', 'addressDeleteRoute'
        ]),

        options: null,

        /**
         * @inheritDoc
         */
        constructor: function AddressWidgetComponent(options) {
            AddressWidgetComponent.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = options;
            AddressWidgetComponent.__super__.initialize.call(this, options);
            widgetManager.getWidgetInstance(this.wid, this._initializeAddress.bind(this));
        },

        _initializeAddress: function(widget) {
            const options = this.options;
            return new Address({
                el: this.options.el,
                addressId: this.options.addressId,
                addressUpdateUrl: function() {
                    return routing.generate(options.addressUpdateRoute.route, {'id': options.addressUpdateRoute.id })
                },
                widget: widget
            });
        }

    });

    return AddressWidgetComponent;
});