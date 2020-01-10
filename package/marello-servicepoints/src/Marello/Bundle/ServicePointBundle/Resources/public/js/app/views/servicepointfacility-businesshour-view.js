define(function(require) {
    'use strict';

    const
        $ = require('jquery'),
        _ = require('underscore'),
        BaseView = require('oroui/js/app/views/base/view');

    /**
     * @export marelloservicepoint/js/app/views/servicepointfacility-businesshour-view
     * @extends oroui.app.views.base.View
     * @class marelloservicepoint.app.views.ServicePointFacilityBusinessHourView
     */
    const ServicePointFacilityBusinessHourView = BaseView.extend({
        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            this.delegate('click', '.businesshours-remove-line-item', this.removeRow);

            ServicePointFacilityBusinessHourView.__super__.initialize.call(this, arguments);
        },

        /**
         * remove single line item
         */
        removeRow: function() {
            this.$el.trigger('content:remove');
            this.remove();
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            ServicePointFacilityBusinessHourView.__super__.dispose.call(this);
        }
    });

    return ServicePointFacilityBusinessHourView;
});
