<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v1_3_1;

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
        $this->modifyMarelloWarehouseTable($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function modifyMarelloWarehouseTable(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_warehouse');
        $table->addColumn('email', 'string', ['notnull' => false, 'length' => 255]);
    }
}
