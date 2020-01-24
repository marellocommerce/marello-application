define([
    'backbone',
    'jquery',
    'underscore',
    'oroui/js/mediator',
    'backgrid',
    'orodatagrid/js/datagrid/select-state-model',
    'orodatagrid/js/datagrid/header-cell/select-all-header-cell'
], function(Backbone, $, _, mediator, Backgrid, SelectStateModel, BaseSelectAllHeaderCell) {
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

        /** @property */
        selectAllStatus: null,

        /** @property */
        allRowsIds: [],

        /** @property */
        selectedRowsIds: [],
        
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
            this.listenTo(mediator, 'select-all-header:reset-select-all-status', function() {
                if ([true, false].indexOf(this.selectAllStatus) > -1) {
                    this.selectAllStatus = null;
                }
            });
            this.listenTo(mediator, 'select-all-header:get-status', function(model, obj) {
                if (self.selectAllStatus === true) {
                    obj.selected = true;
                } else if (self.selectAllStatus === false) {
                    obj.selected = false;
                } else {
                    var rows = self.selectState.get('rows');
                    if (rows.length > 0) {
                        if (rows.indexOf(model.get('id')) > -1 && true === self.selectState.get('inset')) {
                            obj.selected = true;
                        } else if (rows.indexOf(model.get('id')) < 0 && true === self.selectState.get('inset')) {
                            obj.selected = false;
                        } else if (rows.indexOf(model.get('id')) < 0 && false === self.selectState.get('inset')) {
                            obj.selected = true;
                        } else if (rows.indexOf(model.get('id')) > -1 && false === self.selectState.get('inset')) {
                            obj.selected = false;
                        }
                    }
                }
            });
            this.listenTo(mediator, 'boolean-select-row:rendered', function(model) {
                var self = this;
                this.allRowsIds = model.get('allRowsIds');
                var firstModel = this.collection.at(0);
                if (model === firstModel) {
                    $.each(model.get('selectedRows'), function (index, row) {
                        self.selectedRowsIds.push(row['id']);
                    });
                }
                if ([true, false].indexOf(this.selectAllStatus) === -1) {
                    if (model.get('allSelected') === '1' || model.get('allSelected') === true) {
                        self.selectAllStatus = true;
                        self.selectState.set('inset', false);
                    } else if (model.get('allSelected') === '0' || model.get('allSelected') === false) {
                        self.selectAllStatus = false;
                        self.selectState.set('inset', true);
                    }
                }
                if (self.selectAllStatus === true) {
                    self.selectState.set('inset', false);
                } else if (self.selectAllStatus === false) {
                    self.selectState.set('inset', true);
                } else {
                    firstModel = this.collection.at(0);
                    if (model === firstModel && self.selectState.isEmpty()) {
                        $.each(model.get('selectedRows'), function (index, row) {
                            self.selectState.addRow(new Backbone.Model(row));
                        });
                        this.selectedRows = this.selectState.get('rows');
                        if (model.attributes[columnName] === '1' || model.attributes[columnName] === true) {
                            model.attributes[columnName] = true;
                            this.selectState.addRow(model);
                        } else {
                            model.attributes[columnName] = false;
                            this.selectState.removeRow(model);
                        }
                    }
                }
                self.updateState(self.selectState);
            });
            this.updateState(this.selectState);
        },

        onCheckboxClick: function(e) {
            var checkbox = e.target;
            if (checkbox.checked) {
                this.collection.each(function(model) {
                    model.trigger('backgrid:select', model, true);
                });
                this.collection.trigger('includeMultipleRows', this.allRowsIds);
                this.selectAllStatus = true;
                this.selectState.reset();
            } else {
                this.collection.each(function(model) {
                    model.trigger('backgrid:select', model, false);
                });
                this.collection.trigger('excludeMultipleRows', this.allRowsIds);
                this.selectAllStatus = false;
                this.selectState.reset();
            }
            e.stopPropagation();
        },

        onDropdownClick: function(e) {
            var $el = $(e.target);
            if ($el.is('[data-select-all]')) {
                this.collection.each(function(model) {
                    model.trigger('backgrid:select', model, true);
                });
                this.collection.trigger('includeMultipleRows', this.allRowsIds);
                this.selectAllStatus = true;
                this.selectState.reset();
            } else if ($el.is('[data-select-all-visible]')) {
                this.selectState.reset();
                this.collection.trigger('clearState');
                var selectedRows = this.selectedRowsIds.slice();
                var visibleRows = [];
                this.collection.each(function(model) {
                    model.trigger('backgrid:select', model, true);
                    var id = model.get('id');
                    visibleRows.push(id);
                    selectedRows = _.without(selectedRows, id);
                });
                this.collection.trigger('excludeMultipleRows', selectedRows);
                this.selectAllStatus = null;

            } else if ($el.is('[data-select-none]')) {
                this.collection.each(function(model) {
                    model.trigger('backgrid:select', model, false);
                });
                this.collection.trigger('excludeMultipleRows', this.allRowsIds);
                this.selectAllStatus = false;
                this.selectState.reset();
            }
            e.preventDefault();
        },
        
        updateState: function(selectState) {
            var checked = !selectState.get('inset');
            if (this.selectAllStatus === true) {
                checked = true;
            } else if (this.selectAllStatus === false) {
                checked = false;
            }
            this.$('[data-select]:checkbox').prop({
                'indeterminate': !selectState.isEmpty(),
                'checked': checked
            });
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
        }
    });

    return SelectAllHeaderCell;
});
