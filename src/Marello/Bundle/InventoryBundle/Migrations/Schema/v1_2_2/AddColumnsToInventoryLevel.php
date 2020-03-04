<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v1_2_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class AddColumnsToInventoryLevel implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 2;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Table updates **/
        $this->updateInventoryLevelTable($schema);

        $this->updateInventoryLevelForeignKeys($schema);
    }

    /**
     * update current marello_inventory_item table
     *
     * @param Schema $schema
     */
    protected function updateInventoryLevelTable(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_level');
        if (!$table->hasColumn('warehouse_id')) {
            $table->addColumn('warehouse_id', 'integer', ['default' => 1]);
            $table->addUniqueIndex(['inventory_item_id', 'warehouse_id'], 'uniq_40b8d0414584665a5080ecde');
            $table->addIndex(['warehouse_id'], 'idx_40b8d0415080ecde', []);
        }
        if (!$table->hasColumn('updated_at')) {
            $table->addColumn('updated_at', 'datetime');
        }
        if (!$table->hasColumn('organization_id')) {
            $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        }
    }

    /**
     * @param Schema $schema
     */
    protected function updateInventoryLevelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_level');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_warehouse'),
            ['warehouse_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
