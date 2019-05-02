<?php

namespace Marello\Bundle\PurchaseOrderBundle\Migrations\Schema\v1_2_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
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
        $this->updatePurchaseOrderTable($schema, $queries);
    }

    /**
     * Sets constraints on supplier column
     *
     * @param Schema $schema
     */
    protected function updatePurchaseOrderTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_purchase_order');

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_supplier_supplier'),
            ['supplier_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );

        $table->getColumn('supplier_id')->setNotnull(true);
    }
}
