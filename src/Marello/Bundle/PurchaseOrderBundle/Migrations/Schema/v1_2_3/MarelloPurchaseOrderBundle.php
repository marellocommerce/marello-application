<?php

namespace Marello\Bundle\PurchaseOrderBundle\Migrations\Schema\v1_2_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
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

        $table->addColumn('due_date', 'datetime', ['notnull' => false]);
    }
}
