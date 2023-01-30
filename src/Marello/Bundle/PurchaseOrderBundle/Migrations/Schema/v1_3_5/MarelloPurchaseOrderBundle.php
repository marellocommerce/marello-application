<?php

namespace Marello\Bundle\PurchaseOrderBundle\Migrations\Schema\v1_3_5;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

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
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function updatePurchaseOrderTable(Schema $schema)
    {
        $table = $schema->getTable('marello_purchase_order');
        if (!$table->hasColumn('purchase_order_reference')) {
            $table->addColumn('purchase_order_reference', 'string', ['notnull' => false, 'length' => 255]);
        }
    }
}
