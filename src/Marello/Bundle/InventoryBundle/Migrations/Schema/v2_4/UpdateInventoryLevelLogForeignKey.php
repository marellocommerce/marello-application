<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_4;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;

class UpdateInventoryLevelLogForeignKey implements Migration, OrderedMigrationInterface
{
    const INVENTORY_LEVEL_LOG_TABLE_NAME = 'marello_inventory_level_log';
    const INVENTORY_ITEM_TABLE_NAME = 'marello_inventory_item';

    /**
     * @return int
     */
    public function getOrder()
    {
        return 20;
    }

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateInventoryLevelLogForeignKeyConstraint($schema, $queries);
    }

    /**
     * {@inheritdoc}
     * @param Schema $schema
     * @param QueryBag $queries
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function updateInventoryLevelLogForeignKeyConstraint(Schema $schema, $queries)
    {
        $table = $schema->getTable(self::INVENTORY_LEVEL_LOG_TABLE_NAME);
        $table->addForeignKeyConstraint(
            $schema->getTable(self::INVENTORY_ITEM_TABLE_NAME),
            ['inventory_item_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
