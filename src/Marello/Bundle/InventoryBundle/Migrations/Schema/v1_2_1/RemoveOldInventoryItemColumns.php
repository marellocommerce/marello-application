<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v1_2_1;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RemoveOldInventoryItemColumns implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->removeOldRelations($schema);
    }

    /**
     * {@inheritdoc}
     */
    protected function removeOldRelations(Schema $schema)
    {
        $inventoryItemTable = $schema->getTable('marello_inventory_item');
        $this->dropColumns($inventoryItemTable, ['warehouse_id', 'current_level_id']);
        $this->dropIndexes($inventoryItemTable, [
            'uniq_40b8d0414584665a5080ecde',
            'UNIQ_40B8D04178824D09',
            'idx_40b8d0415080ecde',
            'idx_40b8d0414584665a',
        ]);
        $this->dropForeignkeys($inventoryItemTable, [
            'FK_40B8D0415080ECDE',
            'FK_40B8D041C2C9318D'
        ]);
    }

    /**
     * {@inheritdoc}
     * @param Table $table
     * @param array $columns
     */
    private function dropColumns(Table $table, array $columns)
    {
        foreach ($columns as $column) {
            // drop old column from table if exists
            if ($table->hasColumn($column)) {
                $table->dropColumn($column);
            }
        }
    }

    /**
     * {@inheritdoc}
     * @param Table $table
     * @param array $indexes
     */
    private function dropIndexes(Table $table, array $indexes)
    {
        foreach ($indexes as $index) {
            // drop index from table if exists
            if ($table->hasIndex($index)) {
                $table->dropIndex($index);
            }
        }
    }

    /**
     * {@inheritdoc}
     * @param Table $table
     * @param array $foreignKeys
     */
    private function dropForeignkeys(Table $table, array $foreignKeys)
    {
        foreach ($foreignKeys as $foreignKey) {
            // drop key if exist on table
            if ($table->hasForeignKey($foreignKey)) {
                $table->removeForeignKey($foreignKey);
            }
        }
    }
}
