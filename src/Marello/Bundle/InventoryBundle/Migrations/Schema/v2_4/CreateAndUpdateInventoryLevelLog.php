<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_4;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;

class CreateAndUpdateInventoryLevelLog implements Migration, OrderedMigrationInterface
{
    const INVENTORY_LEVEL_LOG_TABLE_NAME = 'marello_inventory_level_log';

    /**
     * @return int
     */
    public function getOrder()
    {
        return 10;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateInventoryLogLevelTable($schema, $queries);
        $this->updateInventoryItemsOnInventoryLevelLog($schema, $queries);
    }

    /**
     * {@inheritdoc}
     * @param Schema $schema
     * @param QueryBag $queries
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function updateInventoryLogLevelTable(Schema $schema, $queries)
    {
        $table = $schema->getTable(self::INVENTORY_LEVEL_LOG_TABLE_NAME);
        $table->addColumn('inventory_item_id', 'integer', ['notnull' => true]);
        $table->addColumn('warehouse_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addIndex(['inventory_item_id']);

        // remove old foreign key of inventory level
        if ($table->hasForeignKey('FK_41E09B7BEBFBF136')) {
            $table->removeForeignKey('FK_41E09B7BEBFBF136');
        }
    }

    /**
     * @param Schema $schema
     * @param $queries
     */
    protected function updateInventoryItemsOnInventoryLevelLog(Schema $schema, $queries)
    {
        // update all inventory_item_id's in the inventoryLevelLog to be able to create the FK later on it
        $query = "
            UPDATE marello_inventory_level_log as inventoryLevelLog
                SET inventory_item_id = (
                  SELECT inventoryItem.id 
                  FROM marello_inventory_item AS inventoryItem, marello_inventory_level as inventoryLevel
                  WHERE inventoryLevelLog.inventory_level_id = inventoryLevel.id
                  AND inventoryLevel.inventory_item_id = inventoryItem.id
                )
            "
        ;

        $queries->addQuery($query);
    }
}
