<?php

namespace Marello\Bundle\PackingBundle\Migrations\Schema\v1_4_2;

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
        if (!$table->hasColumn('saleschannel_name')) {
            $table->addColumn('saleschannel_name', 'string', ['notnull' => false, 'length' => 255]);
        }
    }
}
