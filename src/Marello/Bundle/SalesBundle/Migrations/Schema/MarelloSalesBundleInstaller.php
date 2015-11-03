<?php

namespace Marello\Bundle\SalesBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloSalesBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloSalesEntityNameTable($schema);
        $this->createMarelloSalesSalesChannelTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloSalesEntityNameForeignKeys($schema);
        $this->addMarelloSalesSalesChannelForeignKeys($schema);
    }

    /**
     * Create marello_sales_entity_name table
     *
     * @param Schema $schema
     */
    protected function createMarelloSalesEntityNameTable(Schema $schema)
    {
        $table = $schema->createTable('marello_sales_entity_name');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addIndex(['channel_id'], 'idx_5214fe6d72f5a1aa', []);
        $table->setPrimaryKey(['id']);
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
        $table->addColumn('channeltype', 'string', ['length' => 255]);
        $table->addColumn('createdat', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updatedat', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addIndex(['owner_id'], 'idx_37c71d17e3c61f9', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Add marello_sales_entity_name foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloSalesEntityNameForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_sales_entity_name');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sales_sales_channel'),
            ['channel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
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
            ['onUpdate' => null, 'onDelete' => null]
        );
    }
}
