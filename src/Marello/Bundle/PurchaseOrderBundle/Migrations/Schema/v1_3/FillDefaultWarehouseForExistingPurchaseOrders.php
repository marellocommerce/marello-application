<?php

namespace Marello\Bundle\PurchaseOrderBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;

class FillDefaultWarehouseForExistingPurchaseOrders implements Migration, OrderedMigrationInterface
{
    public function getOrder()
    {
        return 20;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updatePurchaseOrderTable($queries);
    }

    /**
     * Sets constraints on supplier column
     *
     * @param QueryBag $queries
     */
    protected function updatePurchaseOrderTable(QueryBag $queries)
    {
        $queries->addQuery(
            new SqlMigrationQuery(
                "UPDATE marello_purchase_order SET warehouse_id = 
                (
                  SELECT id 
                  FROM marello_inventory_warehouse wh 
                  WHERE wh.is_default = 1
                  LIMIT 1
                )
                WHERE warehouse_id IS NULL"
            )
        );
    }
}
