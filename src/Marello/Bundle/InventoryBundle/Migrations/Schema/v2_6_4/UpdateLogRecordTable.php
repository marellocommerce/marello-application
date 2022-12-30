<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_4;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

class UpdateLogRecordTable implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateMarelloLogRecordTable($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function updateMarelloLogRecordTable(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_level_log');
        if (!$table->hasColumn('inventory_batch')) {
            $table->addColumn('inventory_batch', 'string', ['notnull' => false, 'length' => 255]);
        }
    }
}
