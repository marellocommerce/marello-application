<?php

namespace Marello\Bundle\ZendeskBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MageBridgeBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        //create magento entities
        $this->createMarelloMagentoProductTable($schema);
        $this->createMarelloMagentoProdToWebsiteTable($schema);
        $this->createMarelloMagentoWebsiteTable($schema);
        $this->createMarelloMagentoStoreTable($schema);

        $this->addMarelloMagentoProductForeignKeys($schema);
        $this->addMarelloMagentoProdToWebsiteForeignKeys($schema);
        $this>$this->addMarelloMagentoWebsiteForeignKeys($schema);
        $this->addMarelloMagentoStoreForeignKeys($schema);
    }

    public function updateOroIntegrationTransportTable(Schema $schema)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('api_url', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('admin_url', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('client_id', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('client_secret', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('token_key', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('token_secret', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('salesChannels', 'array', ['notnull' => false, 'comment' => '(DC2Type:array)']);
    }

    /**
     * Create oro_magento_product table
     *
     * @param Schema $schema
     */
    protected function createMarelloMagentoProductTable(Schema $schema)
    {
        $table = $schema->createTable('marello_magento_product');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('sku', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('type', 'string', ['length' => 255]);
        $table->addColumn(
            'special_price',
            'money',
            ['notnull' => false]
        );
        $table->addColumn('price', 'money', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->addColumn('origin_id', 'integer', ['unsigned' => true]);
//        $table->addColumn('cost', 'money', ['notnull' => false]);
        $table->addIndex(['channel_id'], 'IDX_5A17298272F5A1AA', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Add oro_magento_product foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloMagentoProductForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_magento_product');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    /**
     * Add oro_magento_prod_to_website foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloMagentoProdToWebsiteForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_magento_prod_website');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_magento_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_magento_website'),
            ['website_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_magento_website foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloMagentoWebsiteForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_magento_website');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    /**
     * Add oro_magento_store foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloMagentoStoreForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_magento_store');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_magento_website'),
            ['website_id'],
            ['id'],
            ['onDelete' => 'cascade']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    /**
     * Create oro_magento_prod_to_website table
     *
     * @param Schema $schema
     */
    protected function createMarelloMagentoProdToWebsiteTable(Schema $schema)
    {
        $table = $schema->createTable('marello_magento_prod_website');
        $table->addColumn('product_id', 'integer', []);
        $table->addColumn('website_id', 'integer', []);
        $table->addIndex(['product_id'], 'IDX_9BB836554584665A', []);
        $table->addIndex(['website_id'], 'IDX_9BB8365518F45C82', []);
        $table->setPrimaryKey(['product_id', 'website_id']);
    }

    /**
     * Create oro_magento_website table
     *
     * @param Schema $schema
     */
    protected function createMarelloMagentoWebsiteTable(Schema $schema)
    {
        $table = $schema->createTable('marello_magento_website');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('website_code', 'string', ['length' => 32, 'precision' => 0]);
        $table->addColumn('website_name', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'precision' => 0, 'unsigned' => true]);
        $table->addColumn('sort_order', 'integer', ['notnull' => false]);
        $table->addColumn('is_default', 'boolean', ['notnull' => false]);
        $table->addColumn('default_group_id', 'integer', ['notnull' => false]);
        $table->addIndex(['channel_id'], 'IDX_CE3270C872F5A1AA', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['website_name'], 'marello_magento_website_name_idx', []);
        $table->addUniqueIndex(['website_code', 'origin_id', 'channel_id'], 'unq_site_idx');
    }

    /**
     * Create oro_magento_store table
     *
     * @param Schema $schema
     */
    protected function createMarelloMagentoStoreTable(Schema $schema)
    {
        $table = $schema->createTable('marello_magento_store');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('website_id', 'integer', []);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('store_code', 'string', ['length' => 32, 'precision' => 0]);
        $table->addColumn('store_name', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'precision' => 0, 'unsigned' => true]);
        $table->addIndex(['website_id'], 'IDX_477738EA18F45C82', []);
        $table->addIndex(['channel_id'], 'IDX_477738EA72F5A1AA', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['store_code', 'channel_id'], 'unq_code_channel_id');
    }

}
