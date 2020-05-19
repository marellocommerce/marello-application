<?php

namespace Marello\Bundle\Magento2Bundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigModel;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManager;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloMagento2BundleInstaller implements Installation, ExtendExtensionAwareInterface
{
    /**
     * @var ExtendExtension
     */
    private $extendExtension;

    /**
     * @param ExtendExtension $extendExtension
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritDoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateIntegrationTransportTable($schema);

        $this->createWebsiteTable($schema);
        $this->createStoreTable($schema);

        $this->addWebsiteForeignKeys($schema);
        $this->addStoreForeignKeys($schema);

        $this->addSalesChannelWebsiteRelation($schema);
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function updateIntegrationTransportTable(Schema $schema)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('api_url', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('api_token', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('sync_start_date', 'date', ['notnull' => false]);
        $table->addColumn('sync_range', 'string', ['notnull' => false, 'length' => 50]);
        $table->addColumn('initial_sync_start_date', 'datetime', ['notnull' => false]);
        $table->addColumn('websites_sales_channel_mapping', 'json', ['notnull' => false, 'comment' => '(DC2Type:json)']);
    }

    /**
     * @param Schema $schema
     */
    protected function createWebsiteTable(Schema $schema)
    {
        $table = $schema->createTable('marello_magento2_website');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('code', 'string', ['length' => 32, 'precision' => 0]);
        $table->addColumn('name', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'precision' => 0, 'unsigned' => true]);
        $table->addIndex(['channel_id'], 'IDX_D427981972F5A1AA', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['channel_id', 'origin_id'], 'unq_site_idx');
    }

    /**
     * @param Schema $schema
     */
    protected function createStoreTable(Schema $schema)
    {
        $table = $schema->createTable('marello_magento2_store');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('code', 'string', ['length' => 32, 'precision' => 0]);
        $table->addColumn('name', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn('base_currency_code', 'string', ['length' => 3, 'precision' => 0, 'notnull' => false]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'precision' => 0, 'unsigned' => true]);
        $table->addColumn('website_id', 'integer', []);
        $table->addColumn('is_active', 'boolean', ['default' => false]);
        $table->addColumn('locale_id', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('localization_id', 'integer', ['notnull' => false]);
        $table->addIndex(['website_id'], 'IDX_C14EB5DC18F45C82', []);
        $table->addIndex(['channel_id'], 'IDX_C14EB5DC72F5A1AA', []);
        $table->addIndex(['localization_id'], 'IDX_C14EB5DC6A2856C7', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code', 'channel_id'], 'unq_store_code_idx');
    }

    /**
     * @param Schema $schema
     */
    public function addWebsiteForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_magento2_website');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    /**
     * @param Schema $schema
     */
    public function addStoreForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_magento2_store');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_magento2_website'),
            ['website_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_localization'),
            ['localization_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    /**
     * Please check comments to {@see \Marello\Bundle\Magento2Bundle\Model\ExtendWebsite} before modifying this code
     *
     * @param Schema $schema
     */
    protected function addSalesChannelWebsiteRelation(Schema $schema)
    {
        $table = $schema->getTable('marello_sales_sales_channel');
        $targetTable = $schema->getTable('marello_magento2_website');

        $this->extendExtension->addManyToOneRelation(
            $schema,
            $targetTable,
            'salesChannel',
            $table,
            'name',
            [
                ExtendOptionsManager::MODE_OPTION => ConfigModel::MODE_READONLY,
                'extend' => [
                    'is_extend' => true,
                    'owner' => ExtendScope::OWNER_CUSTOM,
                    'without_default' => true,
                    'cascade' => [],
                    'on_delete' => 'SET NULL',
                ],
                'dataaudit' => ['auditable' => true]
            ]
        );

        $this->extendExtension->addManyToOneInverseRelation(
            $schema,
            $targetTable,
            'salesChannel',
            $table,
            'magento2Websites',
            ['name'],
            ['name'],
            ['name'],
            [
                ExtendOptionsManager::MODE_OPTION => ConfigModel::MODE_READONLY,
                'extend' => [
                    'is_extend' => true,
                    'owner' => ExtendScope::OWNER_CUSTOM,
                    'without_default' => true,
                    'on_delete' => 'SET NULL',
                ],
                'datagrid' => ['is_visible' => false],
                'form' => ['is_enabled' => false],
                'view' => ['is_displayable' => false],
                'merge' => ['display' => false],
                'importexport' => ['excluded' => true],
            ]
        );
    }
}
