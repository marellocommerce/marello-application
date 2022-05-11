define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const mediator = require('oroui/js/mediator');
    const BaseView = require('oroui/js/app/views/base/view');
    const ElementsHelper = require('marellocore/js/app/elements-helper');
    const LoadingMaskView = require('oroui/js/app/views/loading-mask-view');
    const StandardConfirmation = require('oroui/js/standart-confirmation');
    const possiblePaymentMethodsTemplate = require('tpl-loader!./../templates/possible-payment-methods-template.html');
    const selectedPaymentMethodTemplate = require('tpl-loader!./../templates/selected-payment-method-template.html');
    const noPaymentMethodsAvailableTemplate = require('tpl-loader!./../templates/no-payment-methods-available.html');
    const NumberFormatter = require('orolocale/js/formatter/number');

    const PossiblePaymentMethodsView = BaseView.extend(_.extend({}, ElementsHelper, {
        autoRender: true,

        options: {
            savedPaymentMethod: null,
            savedPaymentMethodLabel: null,
            possiblePaymentMethodsTemplate: possiblePaymentMethodsTemplate,
            selectedPaymentMethodTemplate: selectedPaymentMethodTemplate,
            noPaymentMethodsAvailableTemplate: noPaymentMethodsAvailableTemplate
        },

        elements: {
            toggleBtn: '[data-role="possible_payment_methods_btn"]',
            possiblePaymentMethodForm: '[data-content="possible_payment_methods_form"]',
            calculatePayment: '[data-name="field__calculate-payment"]',
            paymentMethod: '[data-name="field__payment-method"]',
            paymentMethodOptions: '[data-name="field__payment-method-options"]'
        },

        elementsEvents: {
            toggleBtn: ['click', 'onToggleBtnClick'],
            overriddenPaymentCostAmount: ['change', 'onOverriddenPaymentCostChange'],
            possiblePaymentMethodForm: ['change', 'onPaymentMethodChange'],
            '$form': ['submit', 'onSaveForm']
        },

        initialize: function(options) {
            PossiblePaymentMethodsView.__super__.initialize.apply(this, arguments);

            this.options = $.extend(true, {}, this.options, options || {});
            this.orderHasForPaymentChanged = false;

            this.$form = this.$el.closest('form');
            this.$document = $(document);

            this.initializeElements(options);

            mediator.on('order:form-changes:trigger', this.showLoadingMask, this);
            mediator.on('order:form-changes:load', this.onOrderChange, this);
            mediator.on('order:form-changes:load:after', this.hideLoadingMask, this);
        },

        render: function() {
            this.getElement('possiblePaymentMethodForm').hide();
        },

        onToggleBtnClick: function(e) {
            this.getElement('calculatePayment').val(true);
            mediator.trigger('order:form-changes:trigger', {updateFields: ['possible_payment_methods']});
        },

        onSaveForm: function(e) {
            this.getElement('calculatePayment').val(true);

            var $form = this.getElement('$form');
            $form.validate();
            if ($form.valid() && this.orderHasForPaymentChanged && !this.getElement('overriddenPaymentCostAmount').val()) {
                this.showConfirmation($form);
                return false;
            }

            return true;
        },

        showConfirmation: function(form) {
            this.removeSubview('confirmation');
            this.subview('confirmation', new StandardConfirmation({
                title: _.__('marello.order.possible_payment_methods.confirmation.title'),
                content: _.__('marello.order.possible_payment_methods.confirmation.content'),
                okText: _.__('Save'),
                cancelText: _.__('marello.order.continue_editing')
            }));

            this.subview('confirmation')
                .off('ok').on('ok', _.bind(function() {
                    this.orderHasForPaymentChanged = false;
                    this.getElement('$form').trigger('submit');
                }, this))
                .open();
        },

        showLoadingMask: function() {
            this.orderHasForPaymentChanged = true;
            if (this.getElement('calculatePayment').val()) {
                this.removeSubview('loadingMask');
                this.subview('loadingMask', new LoadingMaskView({
                    container: this.$el
                }));
                this.subview('loadingMask').show();
            }
        },

        hideLoadingMask: function() {
            this.removeSubview('loadingMask');
        },

        onOrderChange: function(e) {
            if (e.totals && _.size(e) === 1) {
                this.orderHasForPaymentChanged = false;
                return;
            }

            if (e.possiblePaymentMethods !== undefined) {
                this.getElement('calculatePayment').val(null);
                this.getElement('toggleBtn').parent('div').hide();
                this.updatePossiblePaymentMethods(e.possiblePaymentMethods);
                this.getElement('possiblePaymentMethodForm').show();
                this.orderHasForPaymentChanged = false;
            } else if (this.recalculationIsNotRequired === true) {
                this.orderHasForPaymentChanged = false;
                this.recalculationIsNotRequired = false;
            } else {
                // this.getElement('possiblePaymentMethodForm').hide();
                this.getElement('toggleBtn').parent('div').show();
                this.orderHasForPaymentChanged = true;
            }
        },

        onOverriddenPaymentCostChange: function() {
            this.recalculationIsNotRequired = true;
            mediator.trigger('order:form-changes:trigger', {updateFields: ['totals']});
        },

        updatePossiblePaymentMethods: function(methods) {
            var selectedMethod = this.getSelectedMethod();
            if (!selectedMethod && this.options.savedPaymentMethod) {
                selectedMethod = this.options.savedPaymentMethod;
            }
            var selectedFound = false;
            var str = this.options.noPaymentMethodsAvailableTemplate();
            if (_.size(methods) > 0) {
                str = this.options.possiblePaymentMethodsTemplate({
                    methods: methods,
                    selectedMethod: selectedMethod,
                    createMethodObject: this.createMethodObject,
                    areMethodsEqual: this.areMethodsEqual,
                    NumberFormatter: NumberFormatter
                });

                selectedFound = this.isMethodAvailable(methods, selectedMethod);
            }

            this.removeSelectedPaymentMethod();
            if (!selectedFound) {
                this.setElementsValue(null);
                if (this.options.savedPaymentMethod) {
                    this.renderPreviousSelectedPaymentMethod();
                }
            }

            this.getElement('possiblePaymentMethodForm').html(str);
        },

        getSelectedMethod: function() {
            var selectedMethod = this.getElement('paymentMethod').val();
            var selectedMethodOptions = this.getElement('paymentMethodOptions').val();
            if (selectedMethod) {
                return this.createMethodObject(selectedMethod, this.deserializeOptions(selectedMethodOptions));
            }
            return null;
        },

        deserializeOptions: function(serializedOptions) {
            serializedOptions = serializedOptions.substring(serializedOptions.indexOf('?')+1).split('&');
            var params = {}, pair, d = decodeURIComponent, i;
            for (i = serializedOptions.length; i > 0;) {
                pair = serializedOptions[--i].split('=');
                params[d(pair[0])] = d(pair[1]);
            }

            return params;
        },

        isMethodAvailable: function(methods, expectedMethod) {
            var selectedFound = false;
            if (!expectedMethod) {
                return selectedFound;
            }
            _.each(methods, function(method) {
                if (method.identifier === expectedMethod.method) {
                    return true;
                }
            }, this);

            return selectedFound;
        },

        /**
         * @param {object|null} method
         */
        setElementsValue: function(method) {
            if (!method) {
                method = this.createMethodObject(null, null);
            }
            this.getElement('paymentMethod').val(method.method);
            this.getElement('paymentMethodOptions').val($.param(method.options));
        },

        removeSelectedPaymentMethod: function() {
            this.$document.find('.selected-payment-method').closest('.control-group').remove();
        },

        renderPreviousSelectedPaymentMethod: function(label) {
            this.removeSelectedPaymentMethod();
            var $prevDiv = $('<div>').html(this.options.selectedPaymentMethodTemplate({
                paymentMethodLabel: _.__('marello.order.previous_payment_method.label'),
                paymentMethodClass: 'selected-payment-method',
                selectedPaymentMethod: this.options.savedPaymentMethodLabel
            }));
            this.$el.closest('.responsive-cell').prepend($prevDiv);
        },

        /**
         * @param {Event} event
         */
        onPaymentMethodChange: function(event) {
            var target = $(event.target);
            var method = this.createMethodObject(
                target.data('payment-method'),
                this.deserializeOptions(target.data('payment-method-options'))
            );

            this.setElementsValue(method);

            this.removeSelectedPaymentMethod();
            if (this.options.savedPaymentMethod && !this.areMethodsEqual(method, this.options.savedPaymentMethod)) {
                this.renderPreviousSelectedPaymentMethod();
            }
        },

        areMethodsEqual: function(methodA, methodB) {
            var equals = false;
            if (methodA && methodB) {
                equals = methodA.method === methodB.method;
                equals = equals && methodA.options === methodB.options;
            }
            return equals;
        },

        createMethodObject: function(method, options) {
            return {
                method: method,
                options: options 
            };
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.disposeElements();

            delete this.$form;
            delete this.$document;
            delete this.options;

            mediator.off(null, null, this);

            PossiblePaymentMethodsView.__super__.dispose.apply(this, arguments);
        }
    }));

    return PossiblePaymentMethodsView;
});
