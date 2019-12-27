<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloInventoryBundleInventoryBatch implements Migration, OrderedMigrationInterface
{
    /**
     * @inheritDoc
     */
    public function getOrder()
    {
        return 30;
    }

    /**
     * @inheritDoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateMarelloInventoryItemTable($schema);
        $this->createMarelloInventoryInventoryBatchTable($schema);
        $this->addMarelloInventoryInventoryBatchForeignKeys($schema);
    }


    /**
     * Create marello_inventory_item table
     *
     * @param Schema $schema
     */
    protected function updateMarelloInventoryItemTable(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_item');
        $table->addColumn('enable_batch_inventory', 'boolean', ['notnull' => false, 'default' => false]);
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
        $table->addColumn('purchase_price', 'money', ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('total_price', 'money', ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('inventory_level_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['inventory_level_id']);
        $table->addUniqueIndex(['batch_number'], 'UNIQ_380BD44456B7924');
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
}