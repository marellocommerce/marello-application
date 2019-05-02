<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloInventoryBundle implements Migration
{
    const TABLE_NAME = 'marello_vrtl_inventory_level';

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable(self::TABLE_NAME)) {
            $schema->dropTable(self::TABLE_NAME);
        }

        $this->createMarelloInventoryBalancedInventoryLevel($schema);
        $this->addMarelloInventoryBalancedInventoryLevelForeignKeys($schema);
    }


    /**
     * @param Schema $schema
     */
    protected function createMarelloInventoryBalancedInventoryLevel(Schema $schema)
    {
        if ($schema->hasTable('marello_blncd_inventory_level')) {
            return;
        }

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
