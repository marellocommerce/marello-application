<?php

namespace Marello\Bundle\MagentoBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtension;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use MarelloMagentoBundle\src\Marello\Bundle\MagentoBundle\Migrations\Schema\v1_0\MarelloMagentoBundle;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class MarelloMagentoBundleInstaller implements
    Installation,
    ActivityExtensionAwareInterface,
    ExtendExtensionAwareInterface,
    ActivityListExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;

    /** @var ExtendExtension $extendExtension */
    protected $extendExtension;

    /** @var ActivityListExtension */
    protected $activityListExtension;

    /**
     * {@inheritdoc}
     */
    public function setActivityListExtension(ActivityListExtension $activityListExtension)
    {
        $this->activityListExtension = $activityListExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

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
        $this->updateIntegrationTransportTable($schema);

        /** Tables generation **/
        $this->createMarelloMagentoWebsiteTable($schema);
        $this->createMarelloMagentoStoreTable($schema);
        $this->createMarelloMagentoProductTable($schema);
        $this->createMarelloMagentoProdToWebsiteTable($schema);
        $this->createMarelloMagentoCategoryTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloMagentoWebsiteForeignKeys($schema);
        $this->addMarelloMagentoStoreForeignKeys($schema);
        $this->addMarelloMagentoProductForeignKeys($schema);
        $this->addMarelloMagentoProdToWebsiteForeignKeys($schema);
        $this->addMarelloMagentoCategoryForeignKeys($schema);

        MarelloMagentoBundle::updateProductOriginId($schema);
    }

    /**
     * Update oro_integration_transport table.
     *
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function updateIntegrationTransportTable(Schema $schema)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('api_url', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('api_user', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('api_key', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('sync_start_date', 'date', ['notnull' => false]);
        $table->addColumn('sync_range', 'string', ['notnull' => false, 'length' => 50]);
        $table->addColumn('website_id', 'integer', ['notnull' => false]);
        $table->addColumn('websites', 'array', ['notnull' => false, 'comment' => '(DC2Type:array)']);
        $table->addColumn('is_extension_installed', 'boolean', ['notnull' => false]);
        $table->addColumn('is_wsi_mode', 'boolean', ['notnull' => false]);
        $table->addColumn('admin_url', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('initial_sync_start_date', 'datetime', ['notnull' => false]);
        $table->addColumn('extension_version', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('magento_version', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('api_token', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn(
            'is_display_order_notes',
            'boolean',
            [
                'notnull' => false,
                'default' => true
            ]
        );
        $table->addColumn('marello_magento_currency', 'string', ['notnull' => false, 'length' => 3]);
    }

    /**
     * Create marello_magento_website table
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
        $table->addColumn('serialized_data', 'text', ['notnull' => false]);
        $table->addIndex(['channel_id'], 'IDX_CE3270C872F5A1AA', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['website_name'], 'marello_magento_website_name_idx', []);
        $table->addUniqueIndex(['website_code', 'origin_id', 'channel_id'], 'unq_site_idx');
    }



    /**
     * Create marello_magento_store table
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
        $table->addColumn('serialized_data', 'text', ['notnull' => false]);
        $table->addIndex(['website_id'], 'IDX_477738EA18F45C82', []);
        $table->addIndex(['channel_id'], 'IDX_477738EA72F5A1AA', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['store_code', 'channel_id'], 'unq_code_channel_id');
    }

    /**
     * Create marello_magento_product table
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
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->addColumn('origin_id', 'integer', ['unsigned' => true]);
        $table->addColumn('cost', 'money', ['notnull' => false]);
        $table->addIndex(['channel_id'], 'IDX_5A17298272F5A1AA', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create marello_mage_prod_to_website table
     *
     * @param Schema $schema
     */
    protected function createMarelloMagentoProdToWebsiteTable(Schema $schema)
    {
        $table = $schema->createTable('marello_mage_prod_to_website');
        $table->addColumn('product_id', 'integer', []);
        $table->addColumn('website_id', 'integer', []);
        $table->addIndex(['product_id'], 'IDX_9BB836554584665A', []);
        $table->addIndex(['website_id'], 'IDX_9BB8365518F45C82', []);
        $table->setPrimaryKey(['product_id', 'website_id']);
    }

    /**
     * Create marello_magento_category table
     *
     * @param Schema $schema
     */
    protected function createMarelloMagentoCategoryTable(Schema $schema)
    {
        $table = $schema->createTable('marello_magento_category');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('category_code', 'string', ['notnull' => false, 'length' => 32, 'precision' => 0]);
        $table->addColumn('category_name', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'precision' => 0, 'unsigned' => true]);
        $table->addColumn('serialized_data', 'text', ['notnull' => false]);
        $table->addIndex(['channel_id'], 'IDX_CE3270C872F5A1AA', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['category_name'], 'marello_magento_category_name_idx', []);
        $table->addUniqueIndex(['category_code', 'origin_id', 'channel_id'], 'unq_site_idx');
    }

    /**
     * Add marello_magento_category foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloMagentoCategoryForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_magento_category');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    /**
     * Add marello_magento_website foreign keys.
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
     * Add marello_magento_store foreign keys.
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
     * Add marello_magento_product foreign keys.
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
     * Add marello_mage_prod_to_website foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloMagentoProdToWebsiteForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_mage_prod_to_website');
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
}
