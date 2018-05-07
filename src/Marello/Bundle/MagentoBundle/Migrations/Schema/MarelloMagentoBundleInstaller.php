<?php

namespace Marello\Bundle\MagentoBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtension;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

//use Marello\Bundle\MagentoBundle\Migrations\Schema\v1_37\CreateActivityAssociation;
//use Marello\Bundle\MagentoBundle\Migrations\Schema\v1_38\InheritanceActivityTargets;

//use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtensionAwareInterface;
//use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtensionTrait;

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
//    CustomerExtensionAwareInterface
{
//    use CustomerExtensionTrait;

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
        return 'v1_52';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloMagentoWebsiteTable($schema);
        $this->createMarelloMagentoStoreTable($schema);
//        $this->updateIntegrationTransportTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloMagentoWebsiteForeignKeys($schema);
        $this->addMarelloMagentoStoreForeignKeys($schema);
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
        $table->addColumn('guest_customer_sync', 'boolean', ['notnull' => false]);
        $table->addColumn('mage_newsl_subscr_synced_to_id', 'integer', ['notnull' => false]);
        $table->addColumn('api_token', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn(
            'is_display_order_notes',
            'boolean',
            [
                'notnull' => false,
                'default' => true
            ]
        );
        $table->addColumn(
            'shared_guest_email_list',
            'simple_array',
            ['notnull' => false, 'comment' => '(DC2Type:simple_array)']
        );
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
        $table->addColumn('serialized_data', 'text', ['notnull' => false]);
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
        $table->addColumn('serialized_data', 'text', ['notnull' => false]);
        $table->addIndex(['website_id'], 'IDX_477738EA18F45C82', []);
        $table->addIndex(['channel_id'], 'IDX_477738EA72F5A1AA', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['store_code', 'channel_id'], 'unq_code_channel_id');
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
}
