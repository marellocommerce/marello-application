<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_5;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;

class UpdateInventoryLevelLogForeignKey implements Migration
{
    const INVENTORY_LEVEL_LOG_TABLE_NAME = 'marello_inventory_level_log';
    const INVENTORY_LEVEL_TABLE_NAME = 'marello_inventory_level';

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
        $table->addIndex(['inventory_level_id']);
        $table->addForeignKeyConstraint(
            $schema->getTable(self::INVENTORY_LEVEL_TABLE_NAME),
            ['inventory_level_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
