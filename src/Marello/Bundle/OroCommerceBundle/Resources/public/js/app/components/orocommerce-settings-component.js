/*jslint nomen:true*/
/*global define*/
define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const routing = require('routing');
    const mediator = require('oroui/js/mediator');
    const LoadingMaskView = require('oroui/js/app/views/loading-mask-view');
    const BaseComponent = require('oroui/js/app/components/base/component');

    const OroCommerceSettingsComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            urlSelector: 'input[name$="[transport][url]"]',
            usernameSelector: 'input[name$="[transport][username]"]',
            keySelector: 'input[name$="[transport][key]"]',
            enterpriseSelector: 'input[name$="[transport][enterprise]"]',
            currencySelector: 'select[name$="[transport][currency]"]',
            businessUnitSelector: 'select[name$="[transport][businessUnit]"]',
            productUnitSelector: 'select[name$="[transport][productUnit]"]',
            customerTaxCodeSelector: 'select[name$="[transport][customerTaxCode]"]',
            priceListSelector: 'select[name$="[transport][priceList]"]',
            productFamilySelector: 'select[name$="[transport][productFamily]"]',
            warehouseSelector: 'select[name$="[transport][warehouse]"]',
            container: '.control-group',
            businessUnitUpdateRoute: '',
            productUnitUpdateRoute: '',
            customerTaxCodeUpdateRoute: '',
            priceListUpdateRoute: '',
            productFamilyUpdateRoute: '',
            warehouseUpdateRoute: '',
            isLoading: false
        },

        /**
         * @property {jquery} $form
         */
        $form: null,

        /**
         * @property {string} selectedShippingService
         */
        $selectedShippingService: null,

        /**
         * @property {string} selectedCategory
         */
        $selectedCategory: null,

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            this.$elem = options._sourceElement;
            this.$form = $(options.formSelector);
            this.selectedBusinessUnit = options.selectedBusinessUnit;
            this.selectedProductUnit = options.selectedProductUnit;
            this.selectedCustomerTaxCode = options.selectedCustomerTaxCode;
            this.selectedPriceList = options.selectedPriceList;
            this.selectedProductFamily = options.selectedProductFamily;
            this.selectedWarehouse = options.selectedWarehouse;

            this.businessUnitLoadingMaskView = new LoadingMaskView({container: this.$elem.find(this.options.businessUnitSelector).closest('.controls')});
            this.productUnitLoadingMaskView = new LoadingMaskView({container: this.$elem.find(this.options.productUnitSelector).closest('.controls')});
            this.customerTaxCodeLoadingMaskView = new LoadingMaskView({container: this.$elem.find(this.options.customerTaxCodeSelector).closest('.controls')});
            this.priceListLoadingMaskView = new LoadingMaskView({container: this.$elem.find(this.options.priceListSelector).closest('.controls')});
            this.productFamilyLoadingMaskView = new LoadingMaskView({container: this.$elem.find(this.options.productFamilySelector).closest('.controls')});
            this.warehouseLoadingMaskView = new LoadingMaskView({container: this.$elem.find(this.options.warehouseSelector).closest('.controls')});
            this.$elem.find(this.options.urlSelector)
                .on('change', _.bind(this.makeChanges, this))
                .trigger('change');
            this.$elem.find(this.options.usernameSelector)
                .on('change', _.bind(this.makeChanges, this))
                .trigger('change');
            this.$elem.find(this.options.keySelector)
                .on('change', _.bind(this.makeChanges, this))
                .trigger('change');
            this.$elem.find(this.options.currencySelector)
                .on('change', _.bind(this.updatePriceLists, this))
                .trigger('change');
            this.$elem.find(this.options.enterpriseSelector)
                .on('change', _.bind(this.toggleWarehousesVisibility, this))
                .trigger('change');
            
            mediator.on('marello_orocommerce:update:productunits', this.updateProductUnits, this);
            mediator.on('marello_orocommerce:update:customertaxcodes', this.updateCustomerTaxCodes, this);
            mediator.on('marello_orocommerce:update:pricelists', this.updatePriceLists, this);
            mediator.on('marello_orocommerce:update:productfamilies', this.updateProductFamilies, this);
            mediator.on('marello_orocommerce:update:warehouses', this.updateWarehouses, this);
        },

        makeChanges: function() {
            // start the first 'trigger' in order to limit the total of requests at the same time
            // other parts are updated because of the triggers
            this.updateBusinessUnits();
        },
        updateBusinessUnits: function() {
            var url = this.$elem.find(this.options.urlSelector).val();
            var username = this.$elem.find(this.options.usernameSelector).val();
            var key = this.$elem.find(this.options.keySelector).val();
            var triggerUpdate = 'productunits';
            if (url !== '' && username !== '' && key !== '') {
                this.updateItem(
                    this.options.businessUnitUpdateRoute,
                    this.options.businessUnitSelector,
                    this.businessUnitLoadingMaskView,
                    this.selectedBusinessUnit,
                    triggerUpdate
                );
            }
        },
        updateProductUnits: function() {
            var url = this.$elem.find(this.options.urlSelector).val();
            var username = this.$elem.find(this.options.usernameSelector).val();
            var key = this.$elem.find(this.options.keySelector).val();
            var triggerUpdate = 'customertaxcodes';
            if (url !== '' && username !== '' && key !== '') {
                this.updateItem(
                    this.options.productUnitUpdateRoute,
                    this.options.productUnitSelector,
                    this.productUnitLoadingMaskView,
                    this.selectedProductUnit,
                    triggerUpdate
                );
            }
        },
        updateCustomerTaxCodes: function() {
            var url = this.$elem.find(this.options.urlSelector).val();
            var username = this.$elem.find(this.options.usernameSelector).val();
            var key = this.$elem.find(this.options.keySelector).val();
            var triggerUpdate = 'pricelists';
            if (url !== '' && username !== '' && key !== '') {
                this.updateItem(
                    this.options.customerTaxCodeUpdateRoute,
                    this.options.customerTaxCodeSelector,
                    this.customerTaxCodeLoadingMaskView,
                    this.selectedCustomerTaxCode,
                    triggerUpdate
                );
            }
        },
        updatePriceLists: function() {
            var url = this.$elem.find(this.options.urlSelector).val();
            var username = this.$elem.find(this.options.usernameSelector).val();
            var key = this.$elem.find(this.options.keySelector).val();
            var currency = this.$elem.find(this.options.currencySelector).val();
            var triggerUpdate = 'productfamilies';
            if (url !== '' && username !== '' && key !== '' && currency !== '') {
                this.updateItem(
                    this.options.priceListUpdateRoute,
                    this.options.priceListSelector,
                    this.priceListLoadingMaskView,
                    this.selectedPriceList,
                    triggerUpdate
                );
            }
        },
        updateProductFamilies: function() {
            var url = this.$elem.find(this.options.urlSelector).val();
            var username = this.$elem.find(this.options.usernameSelector).val();
            var key = this.$elem.find(this.options.keySelector).val();
            var triggerUpdate = 'warehouses';
            if (url !== '' && username !== '' && key !== '') {
                this.updateItem(
                    this.options.productFamilyUpdateRoute,
                    this.options.productFamilySelector,
                    this.productFamilyLoadingMaskView,
                    this.selectedProductFamily,
                    triggerUpdate
                );
            }
        },
        updateWarehouses: function() {
            var url = this.$elem.find(this.options.urlSelector).val();
            var username = this.$elem.find(this.options.usernameSelector).val();
            var key = this.$elem.find(this.options.keySelector).val();
            var enterprise = this.$elem.find(this.options.enterpriseSelector).is(':checked');

            if (url !== '' && username !== '' && key !== '' && enterprise === true) {
                this.updateItem(
                    this.options.warehouseUpdateRoute,
                    this.options.warehouseSelector,
                    this.warehouseLoadingMaskView,
                    this.selectedWarehouse
                );
            }
        },
        toggleWarehousesVisibility: function() {
            var isEnterprise = this.$elem.find(this.options.enterpriseSelector).is(':checked');
            var warehouseSelect = this.$elem.find(this.options.warehouseSelector);
            var warehouseContainer = warehouseSelect.closest('.control-group');
            if (isEnterprise === true) {
                warehouseSelect.removeAttr("disabled");
                warehouseContainer.show();
                this.updateWarehouses();
            } else {
                warehouseSelect.attr('disabled', 'disabled');
                warehouseContainer.hide();
            }
        },
        updateItem: function(route, selector, loadingMaskView, selectedItem, triggerUpdate) {
            var self = this;
            $.ajax({
                url: route,
                type: 'POST',
                data: this.$form.serialize(),
                beforeSend: function() {
                    loadingMaskView.show();
                },
                success: function(json) {
                    $(selector)
                        .closest(self.options.container)
                        .show();
                    $(selector)
                        .find('option')
                        .remove();
                    var selectedExists = false;
                    $(json).each(function(index, data) {
                        if (selectedItem !== null) {
                            if (selectedItem.toString() === data.value) {
                                selectedExists = true
                            }
                        }
                        $(selector)
                            .append('<option value="' + data.value + '">' + data.label + '</option>');
                    });
                    if (selectedItem !== null && selectedExists === true) {
                        $(selector).val(selectedItem);
                    }
                    $(selector).trigger('change');
                },
                complete: function() {
                    loadingMaskView.hide();
                   if (null !== triggerUpdate) {
                       mediator.trigger('marello_orocommerce:update:'+ triggerUpdate);
                   }
                }
            });
        },
        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$elem.off();
            this.$elem.find(this.options.urlSelector).off();
            this.$elem.find(this.options.usernameSelector).off();
            this.$elem.find(this.options.keySelector).off();
            this.$elem.find(this.options.currencySelector).off();
            mediator.off('marello_orocommerce:update:productunits', this.updateProductUnits(), this);
            mediator.off('marello_orocommerce:update:customertaxcodes', this.updateCustomerTaxCodes(), this);
            mediator.off('marello_orocommerce:update:pricelists', this.updatePriceLists(), this);
            mediator.off('marello_orocommerce:update:productfamilies', this.updateProductFamilies(), this);
            mediator.off('marello_orocommerce:update:warehouses', this.updateWarehouses(), this);
            OroCommerceSettingsComponent.__super__.dispose.call(this);
        }
    });

    return OroCommerceSettingsComponent;
});
