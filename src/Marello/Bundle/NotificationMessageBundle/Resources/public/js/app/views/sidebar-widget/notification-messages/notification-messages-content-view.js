
define(function(require) {
    'use strict';

    const $ = require('jquery');
    const mediator = require('oroui/js/mediator');
    const routing = require('routing');
    const LoadingMask = require('oroui/js/app/views/loading-mask-view');
    const BaseView = require('oroui/js/app/views/base/view');
    const template =
        require('tpl-loader!marellonotificationmessage/templates/sidebar-widget/notification-messages/notification-messages-content-view.html');

    const NotificationMessagesContentView = BaseView.extend({
        defaultPerPage: 5,
        defaultTypes: [],

        template: template,

        events: {
            'click .notification-message-widget-row': 'onClickNotificationMessage'
        },

        listen: {
            refresh: 'reloadNotificationMessages'
        },

        /**
         * @inheritdoc
         */
        constructor: function NotificationMessagesContentView(options) {
            NotificationMessagesContentView.__super__.constructor.call(this, options);
        },

        render: function() {
            this.reloadNotificationMessages();
            return this;
        },

        onClickNotificationMessage: function(event) {
            const url = $(event.currentTarget).data('url');
            mediator.execute('redirectTo', {url: url});
        },

        reloadNotificationMessages: function() {
            const view = this;
            const settings = this.model.get('settings');
            settings.perPage = settings.perPage || this.defaultPerPage;
            settings.types = settings.types || this.defaultTypes;

            const routeParams = {
                perPage: settings.perPage,
                types: settings.types,
                r: Math.random()
            };
            const url = routing.generate('marello_notificationmessage_widget_sidebar_notification_messages', routeParams);

            const loadingMask = new LoadingMask({
                container: view.$el
            });
            loadingMask.show();

            $.get(url, function(content) {
                loadingMask.dispose();
                view.$el.html(view.template({content: content}));
            });
        }
    });

    return NotificationMessagesContentView;
});