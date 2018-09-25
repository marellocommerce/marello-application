define([
    'underscore',
    'backbone',
    'orotranslation/js/translator',
    'oroui/js/mediator',
    'oroui/js/messenger',
    'oro/dialog-widget',
    'oroaddress/js/address/view'
], function(
    _,
    Backbone,
    __,
    mediator,
    messenger,
    DialogWidget,
    AddressView
) {
    'use strict';

    var $ = Backbone.$;

    /**
     * @export  marelloaddress/js/address
     * @class   marelloaddress.Address
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        options: {
            'addressUpdateUrl': null,
        },

        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            this.options.addressUpdateUrl = this._getUrl('addressUpdateUrl');
            var editButton = this.$el.find('#edit-address-' + this.options.addressId);
            editButton.on('click', _.bind(this.editAddress, this));
        },

        _getUrl: function(optionsKey) {
            if (_.isFunction(this.options[optionsKey])) {
                return this.options[optionsKey].apply(this, Array.prototype.slice.call(arguments, 1));
            }
            return this.options[optionsKey];
        },

        editAddress: function() {
            this._openAddressEditForm(__('Update Address'), this._getUrl('addressUpdateUrl'));
        },

        getAddressWidget: function() {
            return this.options.widget;
        },

        _openAddressEditForm: function(title, url) {
            if (!this.addressEditDialog) {
                this.addressEditDialog = new DialogWidget({
                    'url': url,
                    'title': title,
                    'regionEnabled': false,
                    'incrementalPosition': false,
                    'dialogOptions': {
                        'modal': true,
                        'resizable': false,
                        'width': 475,
                        'autoResize': true,
                        'close': _.bind(function() {
                            delete this.addressEditDialog;
                        }, this)
                    }
                });
                this.addressEditDialog.render();
                mediator.on(
                    'page:request',
                    _.bind(function() {
                        if (this.addressEditDialog) {
                            this.addressEditDialog.remove();
                        }
                    }, this)
                );
                this.addressEditDialog.on('formSave', _.bind(function() {
                    this.addressEditDialog.remove();
                    messenger.notificationFlashMessage('success', __('Address saved'));
                    this.reloadAddress();
                }, this));
            }
        },

        reloadAddress: function() {
            this.getAddressWidget().render();
        },
    });
});
