<?php

namespace Marello\Bundle\SalesBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
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
        return 'v1_1';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloSalesChannelGroupTable($schema);
        $this->createMarelloSalesSalesChannelTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloSalesChannelGroupForeignKeys($schema);
        $this->addMarelloSalesSalesChannelForeignKeys($schema);
    }
    
    /**
     * @param Schema $schema
     */
    protected function createMarelloSalesChannelGroupTable(Schema $schema)
    {
        $table = $schema->createTable('marello_sales_channel_group');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('system', 'boolean', ['default' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
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
        $table->addColumn('channel_type', 'string', ['length' => 255]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('is_default', 'boolean', []);
        $table->addColumn('code', 'string', ['length' => 255]);
        $table->addColumn('currency', 'string', ['length' => 5]);
        $table->addColumn('localization_id', 'integer', ['notnull' => false]);
        $table->addColumn('locale', 'string', ['notnull' => false, 'length' => 5]);
        $table->addColumn('group_id', 'integer', ['notnull' => false]);
        $table->addColumn('integration_channel_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code'], 'marello_sales_sales_channel_codeidx');
        $table->addUniqueIndex(['integration_channel_id'], 'UNIQ_75C456C9F5B7AF7511');
        $table->addIndex(['owner_id'], 'idx_37c71d17e3c61f9', []);
    }
    
    /**
     * @param Schema $schema
     */
    protected function addMarelloSalesChannelGroupForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_sales_channel_group');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
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
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_localization'),
            ['localization_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sales_channel_group'),
            ['group_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['integration_channel_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }
}
