<?php

namespace Marello\Bundle\PurchaseOrderBundle\Migrations\Schema\v1_3_3;

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
        $this->updatePurchaseOrderItemTable($schema);
    }
    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function updatePurchaseOrderItemTable(Schema $schema)
    {
        $table = $schema->getTable('marello_purchase_order_item');
        if (!$table->hasColumn('organization_id')) {
            $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        }

        if (!$table->hasIndex('IDX_3483BD8632C8A3DE')) {
            // add index to organization column
            $table->addIndex(['organization_id']);
        }

        if (!$table->hasForeignKey('FK_3483BD8632C8A3DE')) {
            // add foreign key constraint
            $table->addForeignKeyConstraint(
                $schema->getTable('oro_organization'),
                ['organization_id'],
                ['id'],
                ['onDelete' => 'SET NULL', 'onUpdate' => null]
            );
        }
    }
}
