<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v1_2_4;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloInventoryBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloInventoryVirtualInventoryLevel($schema);

        /** Foreign keys generation **/
        $this->addMarelloInventoryVirtualInventoryLevelForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloInventoryVirtualInventoryLevel(Schema $schema)
    {
        $table = $schema->createTable('marello_vrtl_inventory_level');
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
    protected function addMarelloInventoryVirtualInventoryLevelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_vrtl_inventory_level');
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
            ['onDelete' => null, 'onUpdate' => null]
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
