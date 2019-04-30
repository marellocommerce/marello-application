<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_1;

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
        $this->addColumnsToInventoryItemTable($schema, $queries);
        $this->addManagedInventoryColumnToInventoryLevelTable($schema, $queries);
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    protected function addColumnsToInventoryItemTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_inventory_item');
        $table->addColumn('backorder_allowed', 'boolean', ['notnull' => false, 'default' => false]);
        $table->addColumn('max_qty_to_backorder', 'integer', ['notnull' => false, 'default' => 0]);
        $table->addColumn('can_preorder', 'boolean', ['notnull' => false, 'default' => false]);
        $table->addColumn('max_qty_to_preorder', 'integer', ['notnull' => false, 'default' => 0]);
        $table->addColumn('back_orders_datetime', 'datetime', ['notnull' => false]);
        $table->addColumn('pre_orders_datetime', 'datetime', ['notnull' => false]);

        $query = "
            UPDATE marello_inventory_item
            SET
              backorder_allowed = FALSE, max_qty_to_backorder = 0, can_preorder = FALSE, max_qty_to_preorder = 0
        ";
        $queries->addQuery($query);
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    protected function addManagedInventoryColumnToInventoryLevelTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_inventory_level');
        $table->addColumn('managed_inventory', 'boolean', ['notnull' => false, 'default' => false]);

        $query = "
            UPDATE marello_inventory_level
            SET
              managed_inventory = FALSE 
        ";
        $queries->addQuery($query);
    }
}
