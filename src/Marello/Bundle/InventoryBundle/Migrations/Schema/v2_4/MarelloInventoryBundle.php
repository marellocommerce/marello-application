<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_4;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloInventoryBundle implements Migration
{
    const INVENTORY_LEVEL_LOG_TABLE_NAME = 'marello_inventory_level_log';
    const INVENTORY_ITEM_TABLE_NAME = 'marello_inventory_item';

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addColumnsToInventoryLogLevelTable($schema, $queries);
    }

    /**
     * {@inheritdoc}
     * @param Schema $schema
     * @param QueryBag $queries
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function addColumnsToInventoryLogLevelTable(Schema $schema, $queries)
    {
        $table = $schema->getTable(self::INVENTORY_LEVEL_LOG_TABLE_NAME);
        $table->addColumn('inventory_item_id', 'integer', ['notnull' => true]);
        $table->addColumn('warehouse_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addIndex(['inventory_item_id']);
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
        //        UPDATE marello_inventory_level_log as main set inventory_item_id = (select t1.id from marello_inventory_item t1, marello_inventory_level as t2 where main.inventory_level_id = t2.id and t2.inventory_item_id = t1.id)
        $queries->addQuery($query);
//        $table->addForeignKeyConstraint(
//            $schema->getTable('marello_inventory_item'),
//            ['inventory_item_id'],
//            ['id'],
//            ['onDelete' => null, 'onUpdate' => null]
//        );

        // remove old foreign key of inventory level
        if ($table->hasForeignKey('FK_41E09B7BEBFBF136')) {
            $table->removeForeignKey('FK_41E09B7BEBFBF136');
        }
    }
}
