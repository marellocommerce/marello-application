<?php

namespace Marello\Bundle\PackingBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

class UpdatePackingSlipTable implements Migration
{
    const MARELLO_PACKING_SLIP_TABLE = 'marello_packing_packing_slip';

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updatePackingSlipTable($schema);
    }

    /**
     * {@inheritdoc}
     * @param Schema $schema
     * @param QueryBag $queries
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function updatePackingSlipTable(Schema $schema)
    {
        $table = $schema->getTable(self::MARELLO_PACKING_SLIP_TABLE);
        if (!$table->hasColumn('source_id')) {
            $table->addColumn('source_id', 'integer', ['notnull' => false]);
        }

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_allocation'),
            ['source_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }
}
