define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const mediator = require('oroui/js/mediator');
    const BaseView = require('oroui/js/app/views/base/view');

    /**
     * @export marelloorder/js/app/views/order-company-view
     * @extends oroui.app.views.base.View
     * @class marelloorder.app.views.OrderCompanyView
     */
    const OrderCompanyView = BaseView.extend({
        /**
         * @property {Object}
         */
        options: {
            selectors: {
                company: '',
            }
        },

        /**
         * @property {jQuery}
         */
        $company: null,

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});
            OrderCompanyView.__super__.initialize.apply(this, arguments);

            this.initLayout();
            this.$company = this.$el.find(this.options.selectors.company)

            mediator.on('order:form-changes:load', this.loadFormChanges, this);
        },

        loadFormChanges: function(response) {
            const company = response['company'] || null;
            if (!company) {
                return;
            }

            const $oldCompany = this.$company;
            this.$company = $(company);

            $oldCompany.parent().trigger('content:remove');
            $oldCompany.inputWidget('dispose');
            $oldCompany.replaceWith(this.$company);

            this.initLayout();
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off('order:form-changes:load', this.loadFormChanges, this);

            OrderCompanyView.__super__.dispose.call(this);
        }
    });

    return OrderCompanyView;
});
