<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v1_0;

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
        $this->createMarelloInventoryItemTable($schema);
        $this->createMarelloInventoryStockLevelTable($schema);
        $this->createMarelloInventoryWarehouseTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloInventoryItemForeignKeys($schema);
        $this->addMarelloInventoryStockLevelForeignKeys($schema);
        $this->addMarelloInventoryWarehouseForeignKeys($schema);
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
        $table->addColumn('warehouse_id', 'integer', []);
        $table->addColumn('current_level_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['product_id', 'warehouse_id'], 'uniq_40b8d0414584665a5080ecde');
        $table->addUniqueIndex(['current_level_id'], 'UNIQ_40B8D04178824D09');
        $table->addIndex(['warehouse_id'], 'idx_40b8d0415080ecde', []);
        $table->addIndex(['product_id'], 'idx_40b8d0414584665a', []);
    }

    /**
     * Create marello_inventory_level table
     *
     * @param Schema $schema
     */
    protected function createMarelloInventoryStockLevelTable(Schema $schema)
    {
        $table = $schema->createTable('marello_inventory_level');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => false]);
        $table->addColumn('inventory', 'integer', []);
        $table->addColumn('inventory_alteration', 'integer', []);
        $table->addColumn('allocated_inventory', 'integer', []);
        $table->addColumn('allocated_inventory_alteration', 'integer', []);
        $table->addColumn('change_trigger', 'string', ['length' => 255]);
        $table->addColumn('subject_type', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('subject_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('inventory_item_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['inventory_item_id'], 'IDX_32D13BA4243D10EA', []);
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
        $table->addColumn('is_default', 'boolean', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['address_id'], 'uniq_15597d1f5b7af75');
        $table->addIndex(['owner_id'], 'idx_15597d17e3c61f9', []);
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
            $schema->getTable('marello_inventory_warehouse'),
            ['warehouse_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_level'),
            ['current_level_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_inventory_level foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloInventoryStockLevelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_level');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_item'),
            ['inventory_item_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
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
    }
}
