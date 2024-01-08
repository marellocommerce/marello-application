define(function(require) {
    'use strict';

    const _ = require('underscore');
    const $ = require('jquery');
    const __ = require('orotranslation/js/translator');
    const BaseView = require('oroui/js/app/views/base/view');
    const WarehouseGridModel = require('marelloenterpriseinventory/js/app/models/warehouse-grid-model');
    const BaseCollection = require('oroui/js/app/models/base/collection');
    const Select2View = require('oroform/js/app/views/select2-view');
    const warehouseGridItemTpl = require('tpl-loader!marelloenterpriseinventory/templates/warehouse-grid-item.html');
    const DeleteConfirmation = require('oroui/js/delete-confirmation');
    require('oroui/js/items-manager/table');
    const WarehouseGridView = BaseView.extend({

        /**
         * @property {Array} Array of codes of currently selected currencies
         */
        allowedWarehouses: null,

        domCache: null,

        events: {
            'click [data-name="warehouse-add"]': 'onAddWarehouse',
            'change .consolidation-warehouse [data-name="field__consolidation-warehouse"]': 'onConsolidationCheckboxChange',
            'change .warehouse-order-on-demand-location [data-name="field__order-on-demand-location-warehouse"]': 'onDemandLocationCheckboxChange',
            'change .warehouse-sort-order-ood [data-name="field__sort-order-ood-warehouse"]': 'onsortOrderOodLocChange'
        },

        /**
         * @inheritdoc
         */
        constructor: function WarehouseGridView(options) {
            WarehouseGridView.__super__.constructor.call(this, options);
        },

        /**
         * @constructor
         */
        initialize: function(options) {
            WarehouseGridView.__super__.initialize.call(this, options);
            this.allowedWarehouses = _.result(options, 'allowedWarehouses');
            this._initCollection();
        },

        _setElement: function(el) {
            WarehouseGridView.__super__._setElement.call(this, el);
            this.createDomCache();
        },

        delegateEvents: function(events) {
            WarehouseGridView.__super__.delegateEvents.call(this, events);
            return this;
        },

        undelegateEvents: function() {
            WarehouseGridView.__super__.undelegateEvents.call(this);
            return this;
        },

        createDomCache: function() {
            this.domCache = {
                $allowedWarehousesInput: this.$('[data-name="field__warehouses"]'),
                $availableWarehousesSelect: this.$('[data-name="warehouse-select"]'),
                $warehouseTable: this.$('[data-name="warehouse-table-body"]'),
                $consolidationWarehouses: $(document).find('[name*=isConsolidationWarehouse]'),
                $onDemandLocationWarehouses: $(document).find('[name*=isOrderOnDemandLocation]'),
                $sortOrderOodLocWarehouses: $(document).find('[name*=sortOrderOodLoc]'),
            };
        },

        _initCollection: function() {
            const allowedWarehouses = this.getAllowedWarehouses();
            const WarehouseCollection = BaseCollection.extend({// eslint-disable-line oro/named-constructor
                model: WarehouseGridModel
            });
            this.collection = new WarehouseCollection(_.map(allowedWarehouses, key => {
                return _.extend(this.allowedWarehouses[key]);
            }));

            this.listenTo(this.collection, 'add remove change', this.onCollectionChange);
            this.updateWarehouses();
        },

        onCollectionChange: function() {
            this.setAllowedWarehouses(this.collection.pluck('id'));
            this.domCache.$availableWarehousesSelect.select2({
                data: this.setAvailableWarehouses(),
                placeholder: __('marelloenterprise.inventory.warehouse.form.select_warehouse')
            });

            this.updateWarehouses();
        },

        onConsolidationCheckboxChange: function(e) {
            const cid = this.$(e.currentTarget).closest('tr').data('cid');
            const propertyValue = this.$(e.currentTarget).is(':checked');
            this.collection.get({cid: cid}).set('isConsolidationWarehouse', propertyValue);
        },

        onDemandLocationCheckboxChange: function(e) {
            const cid = this.$(e.currentTarget).closest('tr').data('cid');
            const propertyValue = this.$(e.currentTarget).is(':checked');
            this.collection.get({cid: cid}).set('isOrderOnDemandLocation', propertyValue);
        },

        onsortOrderOodLocChange: function(e) {
            const cid = this.$(e.currentTarget).closest('tr').data('cid');
            const propertyValue = this.$(e.currentTarget).val();
            this.collection.get({cid: cid}).set('sortOrderOodLoc', propertyValue);
        },

        onAddWarehouse: function(e) {
            e.preventDefault();
            const value = this.domCache.$availableWarehousesSelect.inputWidget('val');
            if (value) {
                this.domCache.$availableWarehousesSelect.inputWidget('val', '');
                const model = new this.collection.model(_.extend(
                    {
                        isConsolidationWarehouse: false,
                        isOrderOnDemandLocation: false,
                        sortOrderOodLoc: 0,
                        onlyAdded: true
                    },
                    this.allowedWarehouses[value]
                ));
                /**
                 * Switch off parameter 'onlyAdded'
                 */
                this.collection.once('add', function(model) {
                    model.set({onlyAdded: false}, {silent: true});
                });
                this.collection.unshift(model);
            }

            this.$el.inputWidget('seekAndCreate');
        },

        updateWarehouses: function() {
            const consolidationWarehousesData = {};
            const orderOnDemandLocationData = {};
            const sortOrderOodLocData = {};
            this.collection.each(function(model) {
                _.extend(consolidationWarehousesData, model.getConsolidationWarehouseData());
                _.extend(orderOnDemandLocationData, model.getOrderOnDemandLocationData());
                _.extend(sortOrderOodLocData, model.getsortOrderOodLocData());
            });

            this.domCache.$consolidationWarehouses.val(JSON.stringify(consolidationWarehousesData));
            this.domCache.$onDemandLocationWarehouses.val(JSON.stringify(orderOnDemandLocationData));
            this.domCache.$sortOrderOodLocWarehouses.val(JSON.stringify(sortOrderOodLocData));
        },

        getConsolidationWarehouses: function() {
            return JSON.parse(this.domCache.$consolidationWarehouses.val());
        },

        setAvailableWarehouses: function() {
            const results = [];
            const allowedWarehouses = this.getAllowedWarehouses();
            _.each(this.allowedWarehouses, (value, key) => {
                if (!_.contains(allowedWarehouses, parseInt(key))) {
                    results.push({
                        id: key,
                        text: value.code + ' - ' + value.name
                    });
                }
            });
            return results;
        },

        getAllowedWarehouses: function() {
            return JSON.parse(this.domCache.$allowedWarehousesInput.val())
        },

        setAllowedWarehouses: function(warehouses) {
            this.domCache.$allowedWarehousesInput.val(JSON.stringify(warehouses));
        },

        removeWarehouse: function(model) {
            const deleteConfirmation = new DeleteConfirmation({
                content: __('marelloenterprise.inventory.warehouse.form.grid.delete_confirmation')
            });

            deleteConfirmation.on('ok', () => {
                this.collection.remove(model);
            });

            deleteConfirmation.open();
        },

        render: function() {
            const getErrorMessage = (errorCode, fieldName) => {
                if (this.validationMessages[errorCode]) {
                    const tmpl = _.template(this.validationMessages[errorCode]);
                    return tmpl({fieldName: fieldName});
                }
                throw new Error('Not supported message for validation error code - ' + errorCode);
            };
            /**
             * Select2 subview requires right value in property "disabled" of $availableWarehousesSelect
             * that's why we set property "disabled" before Select2 will be initialized
             */
            if (this.domCache.$allowedWarehousesInput.is(':disabled')) {
                this.$(':input').prop('disabled', 'disabled');
                this.$el.addClass('disabled');
            }
            this.subview('available-warehouses-select-view', new Select2View({
                el: this.domCache.$availableWarehousesSelect,
                select2Config: {
                    data: this.setAvailableWarehouses(),
                    placeholder: __('marelloenterprise.inventory.warehouse.form.select_warehouse')
                }
            }));
            this.domCache.$warehouseTable.itemsManagerTable({
                collection: this.collection,
                itemTemplate: warehouseGridItemTpl,
                deleteHandler: this.removeWarehouse.bind(this),
                itemRender: function(tmpl, data) {
                    const warehouseModel = new WarehouseGridModel(data);
                    return tmpl(
                        _.extend(
                            warehouseModel.toJSON(),
                            {},
                            {}
                        )
                    );
                }
            }).enableSelection(); // Fixed issue on FF with can't make active rate element on click
        }
    });

    return WarehouseGridView;
});
