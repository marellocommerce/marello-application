define(function(require) {
    'use strict';

    const _ = require('underscore');
    const $ = require('jquery');
    const BaseView = require('oroui/js/app/views/base/view');
    const Popover = require('bootstrap-popover');
    const mediator = require('oroui/js/mediator');
    const layout = require('oroui/js/layout');

    const PaymentRuleMethodsView = BaseView.extend({
        options: {
            addSelector: '.add-method',
            addAllSelector: '.add-all-methods',
            gridSelector: '.payment-methods-grid',
            methodSelectSelector: '[name="marello_payment_methods_configs_rule[method]"]',
            methodSelector: 'input[data-name="field__method"]',
            methodViewSelector: '[data-role="method-view"]',
            methodPreviewSelector: '[data-role="method-preview"]',
            focusFieldsSelector: 'input:text[name]',
            previewFieldsSelector: 'input:text[name],select[name],textarea[name]',
            enabledFieldSelector: '[data-name="field__enabled"]',
            additionalOptionSelector: '.control-group-marello_payment_method_type_config',
            focus: false,
            updateFlag: null
        },

        events: {
            'click .add-all-methods': '_onAddAllClick',
            'click .add-method': '_onAddClick',
            'change [name="marello_payment_methods_configs_rule[method]"]': '_onMethodChange',
            'content:remove': '_onMethodRemove',
            'change textarea': '_onInputsChange'
        },

        listen: {
            'page:afterChange mediator': '_onPageAfterChange'
        },

        methods: null,

        $form: null,

        $methodSelect: null,

        $methodSelectClone: null,

        $add: null,

        $addAll: null,

        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);

            this.$form = $(this.el).closest('form');
            this.$methodSelect = this.$(this.options.methodSelectSelector);
            this.$methodSelectClone = this.$methodSelect.find('option[value!=""]').clone();
            this.$add = this.$(this.options.addSelector);
            this.$addAll = this.$(this.options.addAllSelector);

            this.updateMethodsList();
            this.updateMethodSelect();
            this.updateMethodPreview();
        },

        _onMethodChange: function() {
            this.toggleAddButton();
        },

        _onAddAllClick: function() {
            this.createAddRequest(true);
        },

        _onAddClick: function() {
            this.createAddRequest(false);
        },

        _onInputsChange: function(e) {
            var $method = $(e.target).closest(this.options.methodViewSelector);
            if ($method.length) {
                this.updateMethodPreview($method);
            }
        },

        _onMethodRemove: function(e) {
            var removedMethod = $(e.target).find(this.options.methodSelector).val();
            this.updateMethodsList(removedMethod);
            this.updateMethodSelect();

            if (this.methods.length === 0) {
                this.$(this.options.gridSelector).remove();
            }
        },

        _onPageAfterChange: function() {
            this.focus();
        },

        focus: function() {
            if (!this.options.focus) {
                return;
            }

            var focusFieldsSelector = this.options.focusFieldsSelector;
            this.$(this.options.methodViewSelector).each(function() {
                var $fields = $(this).find(focusFieldsSelector);
                var notEmptyFieldsCount = $fields.filter(function() {
                    return this.value.length > 0;
                }).length;
                if (notEmptyFieldsCount === 0) {
                    $fields.eq(0).attr('autofocus', true).focus();
                    return false;
                }
            });
        },

        toggleAddButton: function() {
            this.$add.toggleClass('disabled', _.isEmpty(this.$methodSelect.val()));
        },

        createAddRequest: function(addAll) {
            var methodCount = this.methods.length - 1;
            var addMethod = function(option) {
                methodCount++;
                return {
                    name: 'marello_payment_methods_configs_rule[methodConfigs][' + methodCount + '][method]',
                    value: option.value
                };
            };

            var data = this.$form.serializeArray();
            if (addAll) {
                data.push.apply(data, this.$methodSelect.find('option[value][value!=""]').get().map(addMethod));
            } else {
                data.push(addMethod(this.$methodSelect.get(0)));
            }
            data.push({
                name: this.options.updateFlag,
                value: true
            });

            mediator.execute('submitPage', {
                url: this.$form.attr('action'),
                type: this.$form.attr('method'),
                data: $.param(data)
            });
        },

        updateMethodsList: function(removedMethod) {
            this.methods = [];
            _.each(this.$(this.options.methodSelector), function(option) {
                if (!removedMethod || option.value !== removedMethod) {
                    this.methods.push(option.value);
                }
            }, this);
        },

        updateMethodSelect: function() {
            this.$methodSelect.empty();
            _.each(this.$methodSelectClone, function(option) {
                if (_.indexOf(this.methods, option.value) === -1) {
                    this.$methodSelect.append($(option).clone());
                }
            }, this);

            var length = this.$methodSelect.find('option').length;
            this.$methodSelect.prop('disabled', length === 0);
            this.$methodSelect.inputWidget('refresh');
            this.$methodSelect.inputWidget('val', '');

            this.$addAll.toggle(length > 1);
            this.toggleAddButton();
        },

        updateMethodPreview: function($method) {
            if (_.isUndefined($method)) {
                _.each(this.$(this.options.methodViewSelector), function(method) {
                    this.updateMethodPreview($(method));
                }, this);
                return;
            }

            var disabled = [];
            $method.find(this.options.enabledFieldSelector).each(function() {
                if ($(this).is(':checkbox') && !this.checked) {
                    disabled.push(this.name.replace(/\[enabled\]$/, ''));
                }
            });

            var $preview = $method.find(this.options.methodPreviewSelector);
            var preview = [];
            _.each($method.find(this.options.previewFieldsSelector), function(field) {
                var $field = $(field);
                var value = _.trim(field.value);
                if (value.length === 0) {
                    return;
                }

                var isDisabled = _.filter(disabled, function(name) {
                        return field.name.indexOf(name) !== -1;
                    }).length > 0;
                if (isDisabled) {
                    return;
                }

                if ($field.is('select')) {
                    value = $field.find('option:selected').text();
                }

                var $label = this.$('label[for="' + field.id + '"]');
                var label = $label.contents().eq(0).text() + ': ';

                preview.push(label + value);
            }, this);

            //replace whitespaces by &nbsp;, for tooltip overflow calculate
            $preview.html(preview.join(', ').replace(/ /g, '&nbsp;'));

            this.updatePreviewTooltip($preview, preview);
        },

        updatePreviewTooltip: function($preview, preview) {
            if ($preview.data(Popover.DATA_KEY) === void 0) {
                layout.initPopoverForElements($preview, {
                    placement: 'bottom',
                    trigger: 'hover',
                    close: false
                }, true);
            }

            var height = $preview.height();
            $preview.css({
                'word-wrap': 'break-word',
                'overflow': 'auto',
                'white-space': 'normal'
            });
            var isOverflow = $preview.height() > height;

            $preview.attr('style', '').data(Popover.DATA_KEY).updateContent(isOverflow ? preview.join('<br/>') : '');
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$form.off(this.eventNamespace());

            delete this.methods;
            delete this.options;

            delete this.$form;
            delete this.$methodSelect;
            delete this.$methodSelectClone;
            delete this.$add;
            delete this.$addAll;

            PaymentRuleMethodsView.__super__.dispose.apply(this, arguments);
        }
    });

    return PaymentRuleMethodsView;
});
