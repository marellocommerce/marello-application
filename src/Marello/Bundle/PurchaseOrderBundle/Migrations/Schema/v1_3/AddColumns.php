<?php

namespace Marello\Bundle\PurchaseOrderBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddColumns implements Migration, OrderedMigrationInterface
{
    public function getOrder()
    {
        return 10;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updatePurchaseOrderTable($schema);
        $this->updatePurchaseOrderItemTable($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function updatePurchaseOrderTable(Schema $schema)
    {
        $table = $schema->getTable('marello_purchase_order');

        $table->addColumn('order_total', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
    }

    /**
     * @param Schema $schema
     */
    protected function updatePurchaseOrderItemTable(Schema $schema)
    {
        $table = $schema->getTable('marello_purchase_order_item');
        $table->addColumn(
            'purchase_price_value',
            'money',
            [
                'precision' => 19,
                'scale' => 4,
                'comment' => '(DC2Type:money)'
            ]
        );
        $table->addColumn('row_total', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
    }
}
