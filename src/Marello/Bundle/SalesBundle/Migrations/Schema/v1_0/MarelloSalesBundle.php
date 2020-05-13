<?php

namespace Marello\Bundle\SalesBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloSalesBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloSalesSalesChannelTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloSalesSalesChannelForeignKeys($schema);
    }

    /**
     * Create marello_sales_sales_channel table
     *
     * @param Schema $schema
     */
    protected function createMarelloSalesSalesChannelTable(Schema $schema)
    {
        $table = $schema->createTable('marello_sales_sales_channel');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', []);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('active', 'boolean', []);
        $table->addColumn('channel_type', 'string', ['length' => 255]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('is_default', 'boolean', []);
        $table->addColumn('code', 'string', ['length' => 255]);
        $table->addColumn('currency', 'string', ['length' => 5]);
        $table->addColumn('localization_id', 'integer', ['notnull' => false]);
        $table->addColumn('locale', 'string', ['notnull' => false, 'length' => 5]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code'], 'marello_sales_sales_channel_codeidx');
        $table->addIndex(['owner_id'], 'idx_37c71d17e3c61f9', []);
    }

    /**
     * Add marello_sales_sales_channel foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloSalesSalesChannelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_sales_sales_channel');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['owner_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_localization'),
            ['localization_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }
}
