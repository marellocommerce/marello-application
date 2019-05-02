<?php

namespace Marello\Bundle\SalesBundle\Migrations\Schema\v1_1;

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
        $this->createMarelloSalesChannelGroupTable($schema);
        $this->modifyMarelloSalesSalesChannelTable($schema);

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
     * @param Schema $schema
     */
    protected function modifyMarelloSalesSalesChannelTable(Schema $schema)
    {
        $table = $schema->getTable('marello_sales_sales_channel');
        $table->addColumn('group_id', 'integer', ['notnull' => false]);
        $table->addColumn('integration_channel_id', 'integer', ['notnull' => false]);
        $table->addUniqueIndex(['integration_channel_id'], 'UNIQ_75C456C9F5B7AF7511');
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
     * @param Schema $schema
     */
    protected function addMarelloSalesSalesChannelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_sales_sales_channel');
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
