/*jslint nomen: true*/
/*global define*/
define(function (require) {
    'use strict';

    var $ = require('jquery'),
        _ = require('underscore'),
        __ = require('orotranslation/js/translator'),
        DeleteConfirmation = require('oroui/js/delete-confirmation');

    return function (options) {
        var $el = options._sourceElement,
            $enableEl = $el.find('#' + options.pricing_enable_id);

        var enableHandler = function (firstLoad) {
            if ($enableEl.is(':checked')) {
                $el.addClass('pricing-enabled');
            } else {
                $el.removeClass('pricing-enabled');
                if(!firstLoad || firstLoad.type === 'click') {
                    handleDeleteConfirmation();
                }
            }
        };

        var handleDeleteConfirmation = function() {
            var message = __('By disabling the Channel Pricing, all data from Channel Pricing will be lost upon saving. Are you sure?');
            var confirm = new DeleteConfirmation({
                content: message,
                okText: __('Yes, I\'m sure')
            });

            confirm.open();

            confirm.on('cancel', function() {
                $el.addClass('pricing-enabled');
                $enableEl.prop('checked',true);
            });
        };

        $enableEl.on('click', enableHandler);

        enableHandler(true);

    };
});
