<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloInventoryBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

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
        $table->addColumn('currentLevel_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['product_id', 'warehouse_id'], 'uniq_40b8d0414584665a5080ecde');
        $table->addUniqueIndex(['currentLevel_id'], 'UNIQ_40B8D04178824D09');
        $table->addIndex(['warehouse_id'], 'idx_40b8d0415080ecde', []);
        $table->addIndex(['product_id'], 'idx_40b8d0414584665a', []);
    }

    /**
     * Create marello_inventory_stock_level table
     *
     * @param Schema $schema
     */
    protected function createMarelloInventoryStockLevelTable(Schema $schema)
    {
        $table = $schema->createTable('marello_inventory_stock_level');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('author_id', 'integer', ['notnull' => false]);
        $table->addColumn('stock', 'integer', []);
        $table->addColumn('allocatedStock', 'integer', []);
        $table->addColumn('changeTrigger', 'string', ['length' => 255]);
        $table->addColumn('subjectType', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('subjectId', 'integer', ['notnull' => false]);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('inventoryItem_id', 'integer', ['notnull' => false]);
        $table->addColumn('previousLevel_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['previousLevel_id'], 'UNIQ_32D13BA4E314A25F');
        $table->addIndex(['inventoryItem_id'], 'IDX_32D13BA4243D10EA', []);
        $table->addIndex(['author_id'], 'IDX_32D13BA4F675F31B', []);
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
            $schema->getTable('marello_inventory_stock_level'),
            ['currentLevel_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * Add marello_inventory_stock_level foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloInventoryStockLevelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_stock_level');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_item'),
            ['inventoryItem_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_stock_level'),
            ['previousLevel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['author_id'],
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
