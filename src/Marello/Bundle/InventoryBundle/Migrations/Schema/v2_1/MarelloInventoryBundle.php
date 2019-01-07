<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_1;

use Doctrine\DBAL\Schema \Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloInventoryBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addAdditionalInventoryLevelFieldsToLog($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function addAdditionalInventoryLevelFieldsToLog(Schema $schema)
    {
        if (!$schema->hasTable('marello_inventory_level_log')) {
            return;
        }

        $table = $schema->getTable('marello_inventory_level_log');
        $table->addColumn('inventory_level_qty', 'integer', ['notnull' => false]);
        $table->addColumn('alloc_inventory_level_qty', 'integer', ['notnull' => false]);
    }
}
