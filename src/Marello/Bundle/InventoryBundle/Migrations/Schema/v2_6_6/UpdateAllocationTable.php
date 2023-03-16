<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_6;

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
        $this->updateAllocationTable($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function updateAllocationTable(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_allocation');
        if (!$table->hasColumn('shipment_id')) {
            $table->addColumn('shipment_id', 'integer', ['notnull' => false]);
            $table->addUniqueIndex(['shipment_id'], 'marello_allocation_shipment_idx');
            $table->addForeignKeyConstraint(
                $schema->getTable('marello_shipment'),
                ['shipment_id'],
                ['id'],
                ['onDelete' => null, 'onUpdate' => null]
            );
        }
    }
}
