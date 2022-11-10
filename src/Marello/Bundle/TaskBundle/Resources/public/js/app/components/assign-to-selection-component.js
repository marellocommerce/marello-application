define(function(require) {
    'use strict';

    const BaseComponent = require('oroui/js/app/components/base/component');
    const _ = require('underscore');

    const AssignToSelectionComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            assignedToContainer: '.assigned-to-container',
            userSelect: 'div[id^="oro_task_assignedToUser-uid"] input[type="hidden"]',
            groupSelect: 'div[id^="oro_task_assignedToGroup-uid"] input[type="hidden"]'
        },

        /**
         * @property {Object}
         */
        $assignedToContainer: null,

        /**
         * @property {Object}
         */
        $userSelect: null,

        /**
         * @property {Object}
         */
        $groupSelect: null,

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            this.$el = options._sourceElement;

            this.$assignedToContainer = this.$el.find(this.options.assignedToContainer);
            this.$userSelect = this.$el.find(this.options.userSelect);
            this.$groupSelect = this.$el.find(this.options.groupSelect);
            this.$el.on('change', this.options.userSelect, _.bind(this.onUserChanged, this));
            this.$el.on('change', this.options.groupSelect, _.bind(this.onGroupChanged, this));
        },

        onUserChanged: function(e) {
            if (e.val) {
                this.$groupSelect.inputWidget('val', '');
            }
        },

        onGroupChanged: function(e) {
            if (e.val) {
                this.$userSelect.inputWidget('val', '');
            }
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$el.off(
                'change',
                _.bind(this.onUserChanged, this)
            );
            this.$el.off(
                'change',
                _.bind(this.onGroupChanged, this)
            );

            AssignToSelectionComponent.__super__.dispose.call(this);
        }
    });

    return AssignToSelectionComponent;
});

