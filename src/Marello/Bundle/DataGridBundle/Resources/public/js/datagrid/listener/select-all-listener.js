define(function(require) {
    'use strict';

    var mediator = require('oroui/js/mediator');
    var SelectAllHeaderCell = require('marellodatagrid/js/datagrid/header-cell/select-all-header-cell');

    return {
        processDatagridOptions: function(deferred, options) {
            mediator.bind('datagrid_create_before', function(options) {
                if (options.metadata.options.rowSelection !== undefined) {
                    var boolColumn = options.metadata.options.rowSelection.columnName;
                    if (boolColumn !== undefined) {
                        for (var i = 0; i < options.columns.length; i++) {
                            var column = options.columns[i];
                            if (column.name === boolColumn) {
                                column.manageable = false;
                                column.headerCell = SelectAllHeaderCell;
                                break;
                            }
                        }
                    }
                }
            });
            deferred.resolve();
        },
        init: function(deferred, options) {
            deferred.resolve();
        }
    };
});