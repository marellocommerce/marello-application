define(function(require) {
    'use strict';

    var BusinessHoursOverridesItemView,
        $ = require('jquery'),
        AbstractItemView = require('marellolayout/js/app/views/abstract-item-view');

    /**
     * @export marelloservicepoint/js/app/views/businesshoursoverrides-item-view
     * @extends marellolayout.app.views.AbstractItemView
     * @class marelloservicepoint.app.views.BusinessHoursOverridesItemView
     */
    BusinessHoursOverridesItemView = AbstractItemView.extend({
        options: {
            ftid: ""
        },

        /**
         * @property {Object}
         */
        data: {},

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            BusinessHoursOverridesItemView.__super__.initialize.apply(this, arguments);
        }
    });

    return BusinessHoursOverridesItemView;
});
