define([
    'jquery',
    'underscore',
    'oroui/js/mediator',
    'backgrid',
    'orodatagrid/js/datagrid/select-state-model',
    'orodatagrid/js/datagrid/header-cell/select-all-header-cell'
], function($, _, mediator, Backgrid, SelectStateModel, BaseSelectAllHeaderCell) {
    'use strict';

    var SelectAllHeaderCell;

    /**
     * Contains mass-selection logic
     *  - watches models selection, keeps reference to selected
     *  - provides mass-selection actions
     *  - listening to models collection events,
     *      fills in 'obj' with proper data for
     *      `backgrid:isSelected` and `backgrid:getSelected`
     *
     * @export  marellodatagrid/js/datagrid/header-cell/select-all-header-cell
     * @class   marellodatagrid.datagrid.headerCell.SelectAllHeaderCell
     * @extends BaseSelectAllHeaderCell
     */
    SelectAllHeaderCell = BaseSelectAllHeaderCell.extend({
        /**
         * Initializer.
         * Subscribers on events listening
         *
         * @param {Object} options
         * @param {Backgrid.Column} options.column
         * @param {Backbone.Collection} options.collection
         */
        initialize: function(options) {
            var debouncedUpdateState = _.bind(_.debounce(this.updateState, 50), this);
            this.column = options.column;
            if (!(this.column instanceof Backgrid.Column)) {
                this.column = new Backgrid.Column(this.column);
            }
            this.selectState = new SelectStateModel();
            this.renderedModels = [];
            var columnName = this.column.attributes.name;
            var self = this;
            this.listenTo(this.selectState, 'change', debouncedUpdateState);
            this.listenTo(mediator, 'boolean-select-row:rendered', function(model) {
                self.renderedModels.push(model);
                if (self.renderedModels.length === self.collection.length) {
                    var selectedCnt = 0;
                    $.each(self.collection.models, function (index, model) {
                        if (model.attributes[columnName] === "1") {
                            model.attributes[columnName] = true;
                            selectedCnt = selectedCnt + 1;
                        } else {
                            model.attributes[columnName] = false;
                        }
                    });
                    if (self.selectState.isEmpty() && selectedCnt === self.collection.length) {
                        self.selectState.set('inset', false);
                    } else {
                        self.selectState.set('inset', true);
                    }
                    self.updateState(self.selectState);
                    self.renderedModels = [];
                }
            });
            this.updateState(this.selectState);
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }
            delete this.selectState;
            delete this.column;
            SelectAllHeaderCell.__super__.dispose.apply(this, arguments);
        },
    });

    return SelectAllHeaderCell;
});
