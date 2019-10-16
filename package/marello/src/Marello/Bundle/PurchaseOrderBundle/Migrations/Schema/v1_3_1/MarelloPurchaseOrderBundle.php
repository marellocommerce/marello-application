<?php

namespace Marello\Bundle\PurchaseOrderBundle\Migrations\Schema\v1_3_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloPurchaseOrderBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updatePurchaseOrderTable($schema);
    }

    /**
     * Sets constraints on supplier column
     *
     * @param Schema $schema
     */
    protected function updatePurchaseOrderTable(Schema $schema)
    {
        $table = $schema->getTable('marello_purchase_order');

        $table->addColumn('data', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
    }
}
