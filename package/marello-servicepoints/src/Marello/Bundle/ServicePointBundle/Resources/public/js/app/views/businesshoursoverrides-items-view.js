define(function(require) {
    'use strict';

    var BusinessHoursOverridesView,
        $ = require('jquery'),
        AbstractItemsView = require('marellolayout/js/app/views/abstract-items-view');

    /**
     * @export marelloservicepoint/js/app/views/businesshoursoverrides-items-view
     * @extends marellolayout.app.views.AbstractItemsView
     * @class marelloservicepoint.app.views.BusinessHoursOverridesView
     */
    BusinessHoursOverridesView = AbstractItemsView.extend({
        /**
         * @property {Object}
         */
        options: {
            data: {},
            route: "marello_order_item_data"
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            console.log('initiialize');
            this.options = $.extend(true, {}, this.options, options || {});
            BusinessHoursOverridesView.__super__.initialize.apply(this, arguments);
        }
    });

    return BusinessHoursOverridesView;
});
