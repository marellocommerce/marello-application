define(function() {
    'use strict';

    const _ = require('underscore');
    const Utils = {
        /**
         * @param {Object} options
         * @param {array} elementKeys
         *
         * @return {array}
         */
        getInvalidJqueryOptionKeys: function (options, elementKeys) {
            let invalidKeys = _.filter(elementKeys, function (elementKey) {
                /**
                 * @var {jQuery} element
                 */
                return _.isUndefined(options[elementKey]) || 0 === options[elementKey].length;
            });

            return invalidKeys;
        },
    };

    return Utils;
});
