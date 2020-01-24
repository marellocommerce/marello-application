define(function(require) {
    'use strict';

    var FormChangesComponent;
    var _ = require('underscore');
    var $ = require('jquery');
    var routing = require('routing');
    var mediator = require('oroui/js/mediator');
    var BaseComponent = require('oroui/js/app/components/base/component');

    /**
     * @export marellolayout/js/app/components/form-changes-component
     * @extends oroui.app.components.base.Component
     * @class marellolayout.app.components.FormChangesComponent
     */
    FormChangesComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            route: null,
            routeParams: {},
            fields: [],
            prefix: null,
            events: {
                before: 'form-changes:load:before',
                load: 'form-changes:load',
                after: 'form-changes:load:after',
                trigger: 'form-changes:trigger',
                init: 'form-changes:init',
                listenersOff: 'form-changes:listeners:off',
                listenersOn: 'form-changes:listeners:on'
            },
            triggerTimeout: 1500
        },

        /**
         * @property {Number}
         */
        timeoutId: null,

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            if (this.options.prefix.length > 0) {
                this.options.events.before = this.options.prefix + ':' + this.options.events.before;
                this.options.events.load = this.options.prefix + ':' + this.options.events.load;
                this.options.events.after = this.options.prefix + ':' + this.options.events.after;
                this.options.events.trigger = this.options.prefix + ':' + this.options.events.trigger;
                this.options.events.init = this.options.prefix + ':' + this.options.events.init;
            }
            this.$el = this.options._sourceElement;

            if (this.options.fields.length > 0) {
                var self = this;
                _.each(this.options.fields, function(field) {
                    self.$el.find(field).attr('data-' + self.options.prefix + '-form-changes-trigger', true);
                });
            }

            this.initializeListener();

            mediator.on(this.options.events.init, this.initializeListener, this);
            mediator.on(this.options.events.trigger, this.callFormChanges, this);
            mediator.on(this.options.events.listenersOff, this.listenerOff, this);
            mediator.on(this.options.events.listenersOn, this.listenerOn, this);
        },

        initializeListener: function() {
            this.listenerOff();
            this.listenerOn();
        },

        listenerOff: function() {
            this.options._sourceElement
                .off('change', '[data-' + this.options.prefix + '-form-changes-trigger]')
                .off('keyup', '[data-' + this.options.prefix + '-form-changes-trigger]');
        },

        listenerOn: function() {
            var callback = _.bind(this.callFormChanges, this);

            var changeCallback = _.bind(function(e) {
                if (this.timeoutId || $(e.target).is('select')) {
                    callback.call(this);
                }

                this.clearTimeout();
            }, this);

            var keyUpCallback = _.bind(function() {
                this.clearTimeout();

                this.timeoutId = setTimeout(_.bind(callback, this), this.options.triggerTimeout);
            }, this);

            this.options._sourceElement
                .on('change', '[data-' + this.options.prefix + '-form-changes-trigger]', changeCallback)
                .on('keyup', '[data-' + this.options.prefix + '-form-changes-trigger]', keyUpCallback);
        },

        clearTimeout: function() {
            if (this.timeoutId) {
                clearTimeout(this.timeoutId);

                this.timeoutId = null;
            }
        },

        callFormChanges: function(e) {
            var self = this;
            var data = this.getData();
            if (e.updateFields !== undefined) {
                _.each(e.updateFields, function(field, index) {
                    data.push({name: 'updateFields[' + index + ']', 'value': field});
                });
            }

            this.listenerOff();
            mediator.trigger(self.options.events.before);

            $.ajax({
                url: routing.generate(this.options.route, this.options.routeParams),
                type: 'POST',
                data: $.param(data),
                success: function(response) {
                    mediator.trigger(self.options.events.load, response);
                    mediator.trigger(self.options.events.after);
                    self.clearTimeout();
                    self.listenerOn();
                },
                error: function() {
                    mediator.trigger(self.options.events.load, {});
                    mediator.trigger(self.options.events.after);
                    self.clearTimeout();
                    self.listenerOn();
                }
            });
        },

        /**
         * @return {Object}
         */
        getData: function() {
            var disabled = this.options._sourceElement.find('input:disabled[' + this.options.prefix + '-form-changes-trigger]')
                .removeAttr('disabled');

            var data = this.options._sourceElement.serializeArray();

            disabled.attr('disabled', 'disabled');

            return data;
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off(this.options.events.init, this.initializeListener, this);
            mediator.off(this.options.events.trigger, this.callFormChanges, this);

            this.listenerOff();

            FormChangesComponent.__super__.dispose.call(this);
        }
    });

    return FormChangesComponent;
});
