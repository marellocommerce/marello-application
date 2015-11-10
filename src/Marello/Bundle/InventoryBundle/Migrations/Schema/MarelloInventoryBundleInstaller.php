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
        $this->createMarelloInventoryWarehouseTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloInventoryItemForeignKeys($schema);
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
        $table->addColumn('warehouse_id', 'integer', []);
        $table->addColumn('product_id', 'integer', []);
        $table->addColumn('quantity', 'integer', []);
        $table->addIndex(['product_id'], 'idx_40b8d0414584665a', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['warehouse_id'], 'idx_40b8d0415080ecde', []);
        $table->addUniqueIndex(['product_id', 'warehouse_id'], 'uniq_40b8d0414584665a5080ecde');
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
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->addColumn('is_default', 'boolean', []);
        $table->addIndex(['owner_id'], 'idx_15597d17e3c61f9', []);
        $table->setPrimaryKey(['id']);
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
            $schema->getTable('marello_inventory_warehouse'),
            ['warehouse_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
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
            ['onUpdate' => null, 'onDelete' => null]
        );
    }
}
