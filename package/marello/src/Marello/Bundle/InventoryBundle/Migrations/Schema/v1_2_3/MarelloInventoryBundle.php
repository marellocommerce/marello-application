<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v1_2_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloInventoryBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloInventoryWarehouseGroupTable($schema);
        $this->modifyMarelloInventoryWarehouseTable($schema);
        $this->createMarelloInventoryWarehouseChannelGroupLinkTable($schema);
        $this->createMarelloInventoryWhChLinkJoinChannelGroupTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloInventoryWarehouseGroupForeignKeys($schema);
        $this->addMarelloInventoryWarehouseForeignKeys($schema);
        $this->addMarelloInventoryWarehouseChannelGroupLinkForeignKeys($schema);
        $this->addMarelloInventoryWhChLinkJoinChannelGroupForeignKeys($schema);
        $this->modifyMarelloInventoryLevelLogTableForeignKeys($schema);
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
        $table->addColumn('system', 'boolean', ['default' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * @param Schema $schema
     */
    protected function modifyMarelloInventoryWarehouseTable(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_warehouse');
        $table->addColumn('group_id', 'integer', ['notnull' => false]);
        $table->addIndex(['group_id'], 'IDX_15597D1FE54D947', []);
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloInventoryWarehouseChannelGroupLinkTable(Schema $schema)
    {
        $table = $schema->createTable('marello_inventory_wh_chg_link');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('system', 'boolean', ['default' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('warehouse_group_id', 'integer', ['notnull' => true]);
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
    protected function addMarelloInventoryWarehouseForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_warehouse');
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
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function modifyMarelloInventoryLevelLogTableForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_level_log');
        if ($table->hasForeignKey('FK_41E09B7BEBFBF136')) {
            $table->removeForeignKey('FK_41E09B7BEBFBF136');
        }

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_level'),
            ['inventory_level_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
