<?php

namespace Marello\Bundle\Magento2Bundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

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
        $this->createCustomerTable($schema);
        $this->createOrderTable($schema);

        $this->createCustomerForeignKeys($schema);
        $this->createOrderForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function createCustomerTable(Schema $schema)
    {
        $table = $schema->createTable('marello_m2_customer');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('channel_id', 'integer');
        $table->addColumn('origin_id', 'integer', [
            'notnull' => false,
            'precision' => 0,
            'unsigned' => true
        ]);
        $table->addColumn('inner_customer_id', 'integer');
        $table->addColumn('hash_id', 'string', ['length' => 32]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['hash_id'], 'idx_m2_customer_hash_id');
        $table->addIndex(['origin_id'], 'idx_m2_customer_origin_id');
    }

    /**
     * @param Schema $schema
     */
    protected function createCustomerForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_m2_customer');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_customer_customer'),
            ['inner_customer_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
    }

    /**
     * @param Schema $schema
     */
    protected function createOrderTable(Schema $schema)
    {
        $table = $schema->createTable('marello_m2_order');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('channel_id', 'integer');
        $table->addColumn('origin_id', 'integer', ['precision' => 0, 'unsigned' => true]);
        $table->addColumn('inner_order_id', 'integer');
        $table->addColumn('m2_customer_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['channel_id', 'origin_id'], 'unq_order_channel_idx');
    }

    /**
     * @param Schema $schema
     */
    protected function createOrderForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_m2_order');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_order_order'),
            ['inner_order_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_m2_customer'),
            ['m2_customer_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }
}
