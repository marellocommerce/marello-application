define(['jquery', 'underscore', 'oroui/js/app/views/base/view', 'lightgallery', 'lightgallery.print'], function($, _, BaseView) {
    'use strict';

    const ImagePopupView = BaseView.extend({
        options: {
        },

        /**
         * @inheritDoc
         */
        constructor: function ImagePopupView() {
            ImagePopupView.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.defaults(
                options || {},
                this.options
            );

            if (options.imageUrl) {
                this.$el.lightgallery();
            }
        }
    });

    return ImagePopupView;
});
