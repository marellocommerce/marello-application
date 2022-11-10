define(function(require) {
    'use strict';

    const $ = require('jquery');
    const mediator = require('oroui/js/mediator');
    const routing = require('routing');
    const LoadingMask = require('oroui/js/app/views/loading-mask-view');
    const BaseView = require('oroui/js/app/views/base/view');
    const template =
        require('tpl-loader!marellotask/templates/sidebar-widget/assigned-allocation-tasks/assigned-tasks-content-view.html');

    const AssignedTasksContentView = BaseView.extend({
        defaultPerPage: 5,

        template: template,

        events: {
            'click .task-widget-row': 'onClickTask'
        },

        listen: {
            refresh: 'reloadTasks'
        },

        /**
         * @inheritdoc
         */
        constructor: function AssignedTasksContentView(options) {
            AssignedTasksContentView.__super__.constructor.call(this, options);
        },

        render: function() {
            this.reloadTasks();
            return this;
        },

        onClickTask: function(event) {
            const taskUrl = $(event.currentTarget).data('url');
            mediator.execute('redirectTo', {url: taskUrl});
        },

        reloadTasks: function() {
            const view = this;
            const settings = this.model.get('settings');
            settings.perPage = settings.perPage || this.defaultPerPage;

            const routeParams = {
                perPage: settings.perPage,
                r: Math.random()
            };
            const url = routing.generate('marello_task_widget_sidebar_allocation_tasks', routeParams);

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

    return AssignedTasksContentView;
});
