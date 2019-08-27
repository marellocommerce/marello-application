<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloInventoryBundle implements Migration
{
    const TABLE_NAME = 'marello_inventory_level';

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addColumnsToInventoryItemTable($schema, $queries);
    }

    protected function addColumnsToInventoryItemTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable(self::TABLE_NAME);
        $table->addColumn('pick_location', 'string', ['length' => 100, 'notnull' => false]);
    }
}
