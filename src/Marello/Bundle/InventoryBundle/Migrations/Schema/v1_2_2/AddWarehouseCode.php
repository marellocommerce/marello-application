<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v1_2_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class AddWarehouseCode implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 3;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Table updates **/
        $this->updateWarehouseTable($schema);
    }

    /**
     * update current marello_inventory_warehouse table
     *
     * @param Schema $schema
     */
    protected function updateWarehouseTable(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_warehouse');
        if (!$table->hasColumn('code')) {
            $table->addColumn('code', 'string', ['length' => 255]);
            $table->addUniqueIndex(['code'], 'UNIQ_15597D177153098');
        }
    }
}
