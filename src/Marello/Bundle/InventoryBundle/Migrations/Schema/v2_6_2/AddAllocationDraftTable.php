<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

class UpdateAllocationDraftTable implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addToMarelloInventoryAllocationDraftItem($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function addToMarelloInventoryAllocationDraftItem(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_alloc_item');
        if (!$table->hasColumn('warehouse_id')) {
            $table->addColumn('warehouse_id', 'integer', ['notnull' => false]);
        }
    }
}
