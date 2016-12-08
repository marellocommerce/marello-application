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
        $this->createMarelloSalesSalesChannelTable($schema);
        $this->createMarelloSalesChannelSupportedLanguagesTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloSalesSalesChannelForeignKeys($schema);
        $this->addMarelloSalesSalesChannelLangForeignKeys($schema);
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
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('is_default', 'boolean', []);
        $table->addColumn('code', 'string', ['length' => 255]);
        $table->addColumn('currency', 'string', ['length' => 5]);
        $table->addColumn('default_language_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code'], 'marello_sales_sales_channel_codeidx');
        $table->addIndex(['owner_id'], 'idx_37c71d17e3c61f9', []);
    }

    /**
     * Create marello_product_saleschannel table
     *
     * @param Schema $schema
     */
    protected function createMarelloSalesChannelSupportedLanguagesTable(Schema $schema)
    {
        $table = $schema->createTable('marello_sales_channel_lang');
        $table->addColumn('sales_channel_id', 'integer', []);
        $table->addColumn('localization_id', 'integer', []);
        $table->setPrimaryKey(['sales_channel_id', 'localization_id']);
        $table->addIndex(['sales_channel_id'], '', []);
        $table->addIndex(['localization_id'], '', []);
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
    }

    /**
     * Add marello_sales_channel_lang foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloSalesSalesChannelLangForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_sales_channel_lang');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sales_sales_channel'),
            ['sales_channel_id'],
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
