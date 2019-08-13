<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloInventoryBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addColumnsToInventoryItemTable($schema, $queries);
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    protected function addColumnsToInventoryItemTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_inventory_item');
        $table->addColumn('order_on_demand_allowed', 'boolean', ['notnull' => false, 'default' => false]);

        $query = "
            UPDATE marello_inventory_item
            SET
              order_on_demand_allowed = FALSE
        ";
        $queries->addQuery($query);
    }
}
