<?php

namespace Marello\Bundle\Magento2Bundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Doctrine\DBAL\Schema\SchemaException;

/**
 * @todo Remove this in final version
 */
class MarelloMagento2Bundle implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateOrderTable($schema);
        $this->createOrderForeignKeys($schema);

        $this->updateCustomerIndexes($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function updateOrderTable(Schema $schema)
    {
        $table = $schema->getTable('marello_m2_order');
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->addColumn('m2_store_id', 'integer', ['notnull' => false]);
    }

    /**
     * @param Schema $schema
     */
    protected function createOrderForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_m2_order');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_m2_store'),
            ['m2_store_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    /**
     * @param Schema $schema
     * @throws SchemaException
     */
    protected function updateCustomerIndexes(Schema $schema)
    {
        $table = $schema->getTable('marello_m2_customer');
        $table->dropIndex('idx_m2_customer_hash_id');
        $table->dropIndex('idx_m2_customer_origin_id');

        $table->addIndex(['channel_id', 'hash_id'], 'idx_customer_hash_channel_idx');
        $table->addUniqueIndex(['channel_id', 'origin_id'], 'unq_customer_channel_idx');
    }
}
