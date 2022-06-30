<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

class UpdateAllocationTable implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createMarelloInventoryAllocation($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloInventoryAllocation(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_allocation');
        if (!$table->hasColumn('source_entity_id')) {
            $table->addColumn('source_entity_id', 'integer', ['notnull' => false]);
        }
    }
}
