<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_6;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

class UpdateAllocationItemTable implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateAllocationTable($schema);
    }

    protected function updateAllocationTable(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_alloc_item');
        $table->addColumn('total_quantity', 'float', ['notnull' => false]);
    }
}
