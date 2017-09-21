define(function (require) {
    'use strict';
    var Select2AutocompleteSystemChannelGroupAwareComponent,
        Select2AutocompleteComponent = require('oro/select2-autocomplete-component');
    Select2AutocompleteSystemChannelGroupAwareComponent = Select2AutocompleteComponent.extend({

        /**
         * @property {Object}
         */
        options: {
            attribute: 'entity-id'
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.$sourceElement = options._sourceElement;
            Select2AutocompleteSystemChannelGroupAwareComponent.__super__.initialize.call(this, options);
        },

        /**
         * Return units from data attribute
         *
         * @returns {jQuery.Element}
         */
        getData: function() {
            return this.$sourceElement.data(this.options.attribute);
        },
        
        makeQuery: function (query) {
            var entityId = this.getData();
            if (entityId.length !== 0) {
                return query + ';' + entityId;
            } else {
                return query;
            }
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            Select2AutocompleteSystemChannelGroupAwareComponent.__super__.dispose.call(this);
        }
    });
    return Select2AutocompleteSystemChannelGroupAwareComponent;
});

