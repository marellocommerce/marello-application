<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_9;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

class MarelloInventoryBundle implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateWarehouseTable($schema);
        $this->updateInventoryBatchTable($schema);
    }

    protected function updateWarehouseTable(Schema $schema): void
    {
        $table = $schema->getTable('marello_inventory_warehouse');
        $table->addColumn('sort_order', 'integer', ['notnull' => false]);
        $table->addColumn('order_on_demand_location', 'bool', ['notnull' => false]);
    }

    protected function updateInventoryBatchTable(Schema $schema): void
    {
        $table = $schema->getTable('marello_inventory_batch');
        $table->addColumn('order_on_demand_ref', 'string', ['notnull' => false, 'length' => 255]);
    }
}
