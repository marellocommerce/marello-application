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
        $this->addManagedInventoryColumnToInventoryLevelTable($schema, $queries);
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
            UPDATE marello_inventory_level il INNER JOIN (
              SELECT * FROM marello_inventory_warehouse wh 
              WHERE wh.warehouse_type <> 'external'
            ) AS wh ON il.warehouse_id = wh.id
            SET
              managed_inventory = FALSE 
        ";
        $queries->addQuery($query);
    }
}
