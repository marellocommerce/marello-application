<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloInventoryBundle implements Migration
{
    const INVENTORY_LEVEL_TABLE_NAME = 'marello_inventory_level';
    const INVENTORY_ITEM_TABLE_NAME = 'marello_inventory_item';

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addColumnsToInventoryItemTable($schema, $queries);
        $this->addColumnsToInventoryLevelTable($schema, $queries);
    }

    /**
     * {@inheritdoc}
     * @param Schema $schema
     * @param QueryBag $queries
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function addColumnsToInventoryItemTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable(self::INVENTORY_ITEM_TABLE_NAME);
        $table->addColumn('order_on_demand_allowed', 'boolean', ['notnull' => false, 'default' => false]);

        $query = "
            UPDATE marello_inventory_item
            SET
              order_on_demand_allowed = FALSE
        ";
        $queries->addQuery($query);
    }

    /**
     * {@inheritdoc}
     * @param Schema $schema
     * @param QueryBag $queries
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function addColumnsToInventoryLevelTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable(self::INVENTORY_LEVEL_TABLE_NAME);
        $table->addColumn('pick_location', 'string', ['length' => 100, 'notnull' => false]);
    }
}
