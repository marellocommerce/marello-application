define(function(require) {
    'use strict';

    var mediator = require('oroui/js/mediator');
    var SelectAllHeaderCell = require('marellodatagrid/js/datagrid/header-cell/select-all-header-cell');

    return {
        processDatagridOptions: function(deferred, options) {
            mediator.bind('datagrid_create_before', function(options) {
                var metadataOptions = options.metadata.options;
                if (metadataOptions.rowSelection !== undefined) {
                    if (metadataOptions.rowSelection.selectAll !== undefined &&
                        metadataOptions.rowSelection.selectAll === true) {
                        var boolColumn = metadataOptions.rowSelection.columnName;
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

                }
            });
            deferred.resolve();
        },
        init: function(deferred, options) {
            deferred.resolve();
        }
    };
});