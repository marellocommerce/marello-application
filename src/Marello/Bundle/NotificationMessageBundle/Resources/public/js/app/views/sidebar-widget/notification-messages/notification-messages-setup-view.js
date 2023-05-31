define(function(require) {
    'use strict';

    const __ = require('orotranslation/js/translator');
    const BaseWidgetSetupView = require('orosidebar/js/app/views/base-widget/base-widget-setup-view');

    const NotificationMessagesSetupView = BaseWidgetSetupView.extend({
        template: require('tpl-loader!marellonotificationmessage/templates/sidebar-widget/notification-messages/notification-messages-setup-view.html'),

        widgetTitle: function() {
            return __('marello.notificationmessage.widget.settings');
        },

        /**
         * @inheritdoc
         */
        constructor: function NotificationMessagesSetupView(options) {
            NotificationMessagesSetupView.__super__.constructor.call(this, options);
        },

        validation: {
            perPage: {
                NotBlank: {},
                Regex: {pattern: '/^\\d+$/'},
                Number: {min: 1, max: 20}
            }
        },

        fetchFromData: function() {
            const data = NotificationMessagesSetupView.__super__.fetchFromData.call(this);
            data.perPage = Number(data.perPage);
            return data;
        }
    });

    return NotificationMessagesSetupView;
});