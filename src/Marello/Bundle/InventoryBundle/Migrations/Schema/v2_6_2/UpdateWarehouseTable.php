<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

class UpdateWarehouseTable implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateMarelloWarehouseTable($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function updateMarelloWarehouseTable(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_warehouse');
        if (!$table->hasColumn('notifier')) {
            $table->addColumn('notifier', 'string', ['notnull' => false, 'length' => 100]);
        }
    }
}
