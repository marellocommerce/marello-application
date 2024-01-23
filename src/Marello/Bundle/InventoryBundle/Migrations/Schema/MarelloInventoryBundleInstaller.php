<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloInventoryBundleInstaller implements Installation, ExtendExtensionAwareInterface, ActivityExtensionAwareInterface
{
    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    /**
     * @var ActivityExtension
     */
    protected $activityExtension;

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v2_6_10';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloInventoryItemTable($schema);
        $this->createMarelloInventoryInventoryLevelTable($schema);
        $this->createMarelloInventoryInventoryBatchTable($schema);
        $this->createMarelloInventoryInventoryLogLevelTable($schema);
        $this->createMarelloInventoryWarehouseTable($schema);
        $this->createMarelloInventoryWarehouseTypeTable($schema);
        $this->createMarelloInventoryWarehouseGroupTable($schema);
        $this->createMarelloInventoryWarehouseChannelGroupLinkTable($schema);
        $this->createMarelloInventoryWhChLinkJoinChannelGroupTable($schema);
        $this->createMarelloInventoryBalancedInventoryLevel($schema);
        $this->createMarelloInventoryAllocation($schema);
        $this->createMarelloInventoryAllocationItem($schema);

        /** Foreign keys generation **/
        $this->addMarelloInventoryItemForeignKeys($schema);
        $this->addMarelloInventoryInventoryLevelForeignKeys($schema);
        $this->addMarelloInventoryInventoryBatchForeignKeys($schema);
        $this->addMarelloInventoryWarehouseForeignKeys($schema);
        $this->addMarelloInventoryWarehouseGroupForeignKeys($schema);
        $this->addMarelloInventoryInventoryLevelLogForeignKeys($schema);
        $this->addMarelloInventoryWarehouseChannelGroupLinkForeignKeys($schema);
        $this->addMarelloInventoryWhChLinkJoinChannelGroupForeignKeys($schema);
        $this->addMarelloInventoryBalancedInventoryLevelForeignKeys($schema);
        $this->addMarelloInventoryAllocationForeignKeys($schema);
        $this->addMarelloInventoryAllocationItemForeignKeys($schema);
    }

    /**
     * Create marello_inventory_item table
     *
     * @param Schema $schema
     */
    protected function createMarelloInventoryItemTable(Schema $schema)
    {
        $table = $schema->createTable('marello_inventory_item');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('product_id', 'integer', []);
        $table->addColumn('desired_inventory', 'integer', ['notnull' => false]);
        $table->addColumn('purchase_inventory', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('backorder_allowed', 'boolean', ['notnull' => false, 'default' => false]);
        $table->addColumn('max_qty_to_backorder', 'integer', ['notnull' => false, 'default' => 0]);
        $table->addColumn('can_preorder', 'boolean', ['notnull' => false, 'default' => false]);
        $table->addColumn('max_qty_to_preorder', 'integer', ['notnull' => false, 'default' => 0]);
        $table->addColumn('back_orders_datetime', 'datetime', ['notnull' => false]);
        $table->addColumn('pre_orders_datetime', 'datetime', ['notnull' => false]);
        $table->addColumn('order_on_demand_allowed', 'boolean', ['notnull' => false, 'default' => false]);
        $table->addColumn('enable_batch_inventory', 'boolean', ['notnull' => false, 'default' => false]);
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'replenishment',
            'marello_inv_reple',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
            ]
        );
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'productUnit',
            'marello_product_unit',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['product_id'], 'UNIQ_40B8D0414584665A', []);
    }

    /**
     * Create marello_inventory_level table
     *
     * @param Schema $schema
     */
    protected function createMarelloInventoryInventoryLevelTable(Schema $schema)
    {
        $table = $schema->createTable('marello_inventory_level');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('inventory', 'integer', []);
        $table->addColumn('allocated_inventory', 'integer', []);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->addColumn('inventory_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('managed_inventory', 'boolean', ['notnull' => false, 'default' => false]);
        $table->addColumn('pick_location', 'string', ['length' => 100, 'notnull' => false]);
        
        $table->setPrimaryKey(['id']);
        $table->addIndex(['inventory_item_id']);
        $table->addColumn('warehouse_id', 'integer', []);
        $table->addUniqueIndex(['inventory_item_id', 'warehouse_id'], 'uniq_40b8d0414584665a5080ecde');
        $table->addIndex(['warehouse_id'], 'idx_40b8d0415080ecde', []);
    }

    /**
     * Create marello_inventory_batch table
     *
     * @param Schema $schema
     */
    protected function createMarelloInventoryInventoryBatchTable(Schema $schema)
    {
        $table = $schema->createTable('marello_inventory_batch');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('batch_number', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('batch_reference', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('purchase_reference', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('quantity', 'integer', []);
        $table->addColumn('delivery_date', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('expiration_date', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('sell_by_date', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('purchase_price', 'money', ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('total_price', 'money', ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('inventory_level_id', 'integer', ['notnull' => false]);
        $table->addColumn('order_on_demand_ref', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['batch_number', 'inventory_level_id'], 'UNIQ_380BD44456B7924');
    }

    /**
     * Create marello_inventory_level_log table
     *
     * @param Schema $schema
     */
    protected function createMarelloInventoryInventoryLogLevelTable(Schema $schema)
    {
        $table = $schema->createTable('marello_inventory_level_log');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => false]);
        $table->addColumn('inventory_alteration', 'integer', []);
        $table->addColumn('allocated_inventory_alteration', 'integer', []);
        $table->addColumn('change_trigger', 'string', ['length' => 255]);
        $table->addColumn('subject_type', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('subject_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->addColumn('inventory_level_id', 'integer', ['notnull' => false]);
        $table->addColumn('inventory_item_id', 'integer', ['notnull' => true]);
        $table->addColumn('warehouse_name', 'string', ['notnull' => true, 'length' => 255]);
        $table->addColumn('inventory_batch', 'string', ['notnull' => false, 'length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['inventory_item_id']);
        $table->addIndex(['inventory_level_id']);
        $table->addIndex(['user_id'], 'IDX_32D13BA4F675F31B', []);
    }

    /**
     * Create marello_inventory_warehouse table
     *
     * @param Schema $schema
     */
    protected function createMarelloInventoryWarehouseTable(Schema $schema)
    {
        $table = $schema->createTable('marello_inventory_warehouse');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', []);
        $table->addColumn('address_id', 'integer', ['notnull' => false]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->addColumn('code', 'string', ['length' => 255]);
        $table->addColumn('is_default', 'boolean', []);
        $table->addColumn('warehouse_type', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('group_id', 'integer', ['notnull' => false]);
        $table->addColumn('email', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('notifier', 'string', ['notnull' => false, 'length' => 100]);
        $table->addColumn('sort_order_ood_loc', 'integer', ['notnull' => false]);
        $table->addColumn('order_on_demand_location', 'boolean', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['address_id'], 'uniq_15597d1f5b7af75');
        $table->addUniqueIndex(['code'], 'UNIQ_15597D177153098');
        $table->addIndex(['owner_id'], 'IDX_15597d17e3c61f9', []);
        $table->addIndex(['group_id'], 'IDX_15597D1FE54D947', []);
    }

    /**
     * Create marello_inventory_wh_type table
     *
     * @param Schema $schema
     */
    protected function createMarelloInventoryWarehouseTypeTable(Schema $schema)
    {
        if (!$schema->hasTable('marello_inventory_wh_type')) {
            $table = $schema->createTable('marello_inventory_wh_type');
            $table->addColumn('name', 'string', ['length' => 32]);
            $table->addColumn('label', 'string', ['length' => 255]);
            $table->setPrimaryKey(['name']);
            $table->addUniqueIndex(['label'], 'UNIQ_629E2BBEA750E8');
        }
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloInventoryWarehouseGroupTable(Schema $schema)
    {
        $table = $schema->createTable('marello_inventory_wh_group');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('is_system', 'boolean', ['default' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloInventoryWarehouseChannelGroupLinkTable(Schema $schema)
    {
        $table = $schema->createTable('marello_inventory_wh_chg_link');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('is_system', 'boolean', ['default' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('warehouse_group_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addUniqueIndex(['warehouse_group_id'], 'UNIQ_2AC24B90DE1CBBE1');
        $table->setPrimaryKey(['id']);
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloInventoryWhChLinkJoinChannelGroupTable(Schema $schema)
    {
        $table = $schema->createTable('marello_inventory_lnk_join_chg');
        $table->addColumn('link_id', 'integer', ['notnull' => true]);
        $table->addColumn('channel_group_id', 'integer', ['notnull' => true]);
        $table->addUniqueIndex(['channel_group_id'], 'UNIQ_629E2BBEA750E85');
        $table->setPrimaryKey(['link_id', 'channel_group_id']);
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloInventoryBalancedInventoryLevel(Schema $schema)
    {
        $table = $schema->createTable('marello_blncd_inventory_level');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('inventory_qty', 'integer', ['notnull' => true]);
        $table->addColumn('blncd_inventory_qty', 'integer', ['notnull' => true]);
        $table->addColumn('reserved_inventory_qty', 'integer', ['notnull' => false]);
        $table->addColumn('product_id', 'integer', ['notnull' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('channel_group_id', 'integer', ['notnull' => true]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['product_id', 'channel_group_id'], 'UNIQ_BDB9A2F64584665A89E4AAEE');
        $table->addIndex(['channel_group_id'], 'IDX_BDB9A2F689E4AAEE', []);
        $table->addIndex(['product_id'], 'IDX_BDB9A2F64584665A', []);
        $table->addIndex(['organization_id']);
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloInventoryAllocation(Schema $schema)
    {
        $table = $schema->createTable('marello_inventory_allocation');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('order_id', 'integer', ['notnull' => true]);
        $table->addColumn('shipping_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('warehouse_id', 'integer', ['notnull' => false]);
        $table->addColumn('parent_id', 'integer', ['notnull' => false]);
        $table->addColumn('source_entity_id', 'integer', ['notnull' => false]);
        $table->addColumn('allocation_number', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('shipment_id', 'integer', ['notnull' => false]);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['shipment_id'], 'marello_allocation_shipment_idx');

        $this->activityExtension->addActivityAssociation($schema, 'marello_notification', $table->getName());
        $this->activityExtension->addActivityAssociation($schema, 'oro_email', $table->getName());
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_task', $table->getName());
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'status',
            'marello_allocation_status',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'state',
            'marello_allocation_state',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'allocationContext',
            'marello_allocation_allocationcontext',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'reshipmentReason',
            'marello_allocation_reshipmentreason',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloInventoryAllocationItem(Schema $schema)
    {
        $table = $schema->createTable('marello_inventory_alloc_item');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('allocation_id', 'integer', ['notnull' => true]);
        $table->addColumn('product_id', 'integer', ['notnull' => false]);
        $table->addColumn('product_name', 'string', ['length' => 255]);
        $table->addColumn('product_sku', 'string', ['length' => 255]);
        $table->addColumn('order_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('warehouse_id', 'integer', ['notnull' => false]);
        $table->addColumn('quantity', 'float', ['notnull' => true]);
        $table->addColumn('total_quantity', 'float', ['notnull' => false]);
        $table->addColumn('quantity_confirmed', 'float', ['notnull' => false]);
        $table->addColumn('quantity_rejected', 'float', ['notnull' => false]);
        $table->addColumn('comment', 'text', ['notnull' => false]);
        $table->addColumn('inventory_batches', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['organization_id']);
    }

    /**
     * Add marello_inventory_item foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloInventoryItemForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_item');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_inventory_level foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloInventoryInventoryLevelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_level');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_item'),
            ['inventory_item_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_warehouse'),
            ['warehouse_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_inventory_batch foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloInventoryInventoryBatchForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_batch');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_level'),
            ['inventory_level_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_inventory_level foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloInventoryInventoryLevelLogForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_level_log');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_item'),
            ['inventory_item_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_level'),
            ['inventory_level_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * Add marello_inventory_warehouse foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloInventoryWarehouseForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_warehouse');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['owner_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_address'),
            ['address_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_wh_type'),
            ['warehouse_type'],
            ['name'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_wh_group'),
            ['group_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloInventoryWarehouseGroupForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_wh_group');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloInventoryWarehouseChannelGroupLinkForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_wh_chg_link');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_wh_group'),
            ['warehouse_group_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }
    
    /**
     * @param Schema $schema
     */
    protected function addMarelloInventoryWhChLinkJoinChannelGroupForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_lnk_join_chg');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_wh_chg_link'),
            ['link_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sales_channel_group'),
            ['channel_group_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloInventoryBalancedInventoryLevelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_blncd_inventory_level');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sales_channel_group'),
            ['channel_group_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloInventoryAllocationForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_allocation');
        $table->addForeignKeyConstraint(
            $table,
            ['parent_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_order_order'),
            ['order_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_address'),
            ['shipping_address_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_warehouse'),
            ['warehouse_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_shipment'),
            ['shipment_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloInventoryAllocationItemForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_alloc_item');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_order_order_item'),
            ['order_item_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_allocation'),
            ['allocation_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_warehouse'),
            ['warehouse_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Sets the ExtendExtension
     *
     * @param ExtendExtension $extendExtension
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * Sets the ActivityExtension
     *
     * @param ActivityExtension $activityExtension
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }
}
