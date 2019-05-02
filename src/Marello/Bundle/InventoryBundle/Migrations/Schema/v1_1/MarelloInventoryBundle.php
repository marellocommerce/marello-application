<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v1_1;

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
        /** Table generation **/
        $this->createMarelloInventoryWarehouseTypeTable($schema);

        /** Update existing table */
        $this->updateMarelloInventoryWarehouseTable($schema);

        /** update foreign key for warehouse table */
        $this->addMarelloInventoryWarehouseTypeForeignKeys($schema);
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
            $table->addUniqueIndex(['label'], '');
        }
    }

    /**
     * Update existing Warehouse table
     * @param Schema $schema
     */
    protected function updateMarelloInventoryWarehouseTable(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_warehouse');
        $table->addColumn('warehouse_type', 'string', ['notnull' => false, 'length' => 32]);
        $table->addIndex(['warehouse_type']);
    }

    /**
     * Add marello_inventory_wh_type foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloInventoryWarehouseTypeForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_warehouse');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_wh_type'),
            ['warehouse_type'],
            ['name'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }
}
