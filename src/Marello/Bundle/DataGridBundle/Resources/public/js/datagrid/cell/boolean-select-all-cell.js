define([
    'jquery',
    'oro/datagrid/cell/boolean-cell',
    'orotranslation/js/translator',
    'orodatagrid/js/datagrid/header-cell/select-all-header-cell'
], function($, BooleanCell, __, SelectAllHeaderCell) {
    'use strict';

    var BooleanSelectAllCell;

    /**
     * Boolean column cell. Added missing behaviour.
     *
     * @export  oro/datagrid/cell/boolean-select-all-cell
     * @class   oro.datagrid.cell.BooleanSelectAllCell
     * @extends BooleanCell
     */
    BooleanSelectAllCell = BooleanCell.extend({
        /** @property */
        checkboxSelector: '[data-role="select-row-cell"]',

        /** @property */
        /*events: {
            'change :checkbox': 'onChange',
            'click': 'enterEditMode'
        },*/

        /**
         * @inheritDoc
         */
        initialize: function (options) {
            BooleanSelectAllCell.__super__.initialize.apply(this, arguments);
            this.column = options.column;
            if (!(this.column instanceof Backgrid.Column)) {
                this.column = new Backgrid.Column(this.column);
            }
            this.listenTo(this.model, 'backgrid:select', function(model, checked) {
                BooleanCell.__super__.enterEditMode.apply(this, arguments);
                if (this.column.get('editable')) {
                    //var $editor = this.currentEditor.$el;
                    this.$(':checkbox').prop('checked', checked).change();
                }

            });
        },

        /**
         * When the checkbox's value changes, this method will trigger a Backbone
         * `backgrid:selected` event with a reference of the model and the
         * checkbox's `checked` value.
         */
        onChange: function(e) {
            this.model.trigger('backgrid:selected', this.model, $(e.target).prop('checked'));
        },
        
        /**
         * @inheritDoc
         */
        render: function() {
            if (this.column.get('editable')) {
                this.$el.empty();
                var model = this.model, column = this.column;
                var editable = Backgrid.callByNeed(column.editable(), column, model);
                var rawData = this.formatter.fromRaw(model.get(column.get("name")), model);
                this.$el.addClass('grid-body-cell-massAction');
                this.$el.append($("<input>", {
                    tabIndex: -1,
                    type: "checkbox",
                    'data-role': "select-row-cell",
                    checked: rawData !== '0',
                    disabled: !editable
                }));
                //var state = {selected: rawData !== '0'};
                //this.model.trigger('backgrid:select', this.model, state);
                this.delegateEvents();
                //this.$checkbox = this.$el.find(this.checkboxSelector);
                this.model.trigger('backgrid:select', this.model, this.$el.find(this.checkboxSelector).prop('checked'));
                return this;
            } else {
                // render a yes/no text for non editable cell
                this.$el.empty();
                var text = '';
                var columnData = this.model.get(this.column.get('name'));
                if (columnData !== null) {
                    text = this.formatter.fromRaw(columnData) ? __('Yes') : __('No');
                }
                this.$el.append('<span>').text(text);
                this.delegateEvents();
            }

            return this;
        }
    });

    return BooleanSelectAllCell;
});
