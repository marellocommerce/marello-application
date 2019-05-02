<?php

namespace Marello\Bundle\PurchaseOrderBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddForeignKeyForWarehouseIdColumn implements Migration, OrderedMigrationInterface
{
    public function getOrder()
    {
        return 30;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addPurchaseOrderForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function addPurchaseOrderForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_purchase_order');
        $table->changeColumn('warehouse_id', ['notnull' => true]);
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_warehouse'),
            ['warehouse_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }
}
