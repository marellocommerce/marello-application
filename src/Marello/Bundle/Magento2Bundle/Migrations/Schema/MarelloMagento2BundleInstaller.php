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
        return 'v1_4';
    }

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateIntegrationTransportTable($schema);

        $this->createWebsiteTable($schema);
        $this->createStoreTable($schema);

        $this->createWebsiteForeignKeys($schema);
        $this->createStoreForeignKeys($schema);

        $this->createSalesChannelWebsiteRelation($schema);

        $this->createMagentoProductTable($schema);
        $this->createMagentoProductForeignKeys($schema);

        $this->createMagentoProductTaxTable($schema);
        $this->createProductClassToTaxCodeRelation($schema);
        $this->createMagentoProductTaxForeignKeys($schema);

        $this->createCustomerTable($schema);
        $this->createOrderTable($schema);

        $this->createCustomerForeignKeys($schema);
        $this->createOrderForeignKeys($schema);

        $this->createMagentoAttributeSetTable($schema);
        $this->addMagentoAttributeSetForeignKeys($schema);
        $this->addAttributeSetToAttributeFamilyRelation($schema);
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function updateIntegrationTransportTable(Schema $schema)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('m2_api_url', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('m2_api_token', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('m2_sync_start_date', 'date', ['notnull' => false]);
        $table->addColumn('m2_sync_range', 'string', ['notnull' => false, 'length' => 50]);
        $table->addColumn('m2_initial_sync_start_date', 'datetime', ['notnull' => false]);
        $table->addColumn('m2_websites_sales_channel_map', 'json', [
            'notnull' => false,
            'comment' => '(DC2Type:json)'
        ]);
        $table->addColumn('m2_del_remote_data_on_deact', 'boolean', ['notnull' => false]);
        $table->addColumn('m2_del_remote_data_on_del', 'boolean', ['notnull' => false]);
    }

    /**
     * @param Schema $schema
     */
    protected function createWebsiteTable(Schema $schema)
    {
        $table = $schema->createTable('marello_m2_website');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('channel_id', 'integer');
        $table->addColumn('code', 'string', ['length' => 32, 'precision' => 0]);
        $table->addColumn('name', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn('origin_id', 'integer', [
            'notnull' => false,
            'precision' => 0,
            'unsigned' => true
        ]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['channel_id', 'origin_id'], 'unq_site_idx');
    }

    /**
     * @param Schema $schema
     */
    protected function createStoreTable(Schema $schema)
    {
        $table = $schema->createTable('marello_m2_store');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('channel_id', 'integer');
        $table->addColumn('code', 'string', ['length' => 32, 'precision' => 0]);
        $table->addColumn('name', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn('base_currency_code', 'string', [
            'length' => 3,
            'precision' => 0,
            'notnull' => false
        ]);
        $table->addColumn('origin_id', 'integer', [
            'notnull' => false,
            'precision' => 0,
            'unsigned' => true
        ]);
        $table->addColumn('website_id', 'integer', []);
        $table->addColumn('is_active', 'boolean', ['default' => false]);
        $table->addColumn('locale_id', 'string', [
            'notnull' => false,
            'length' => 255,
            'precision' => 0
        ]);
        $table->addColumn('localization_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code', 'channel_id'], 'unq_store_code_idx');
    }

    /**
     * @param Schema $schema
     */
    public function createWebsiteForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_m2_website');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
    }

    /**
     * @param Schema $schema
     */
    public function createStoreForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_m2_store');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_m2_website'),
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
    protected function createSalesChannelWebsiteRelation(Schema $schema)
    {
        $table = $schema->getTable('marello_sales_sales_channel');
        $targetTable = $schema->getTable('marello_m2_website');

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

    /**
     * @param Schema $schema
     */
    protected function createMagentoProductTable(Schema $schema)
    {
        $table = $schema->createTable('marello_m2_product');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('sku', 'string', ['length' => 255]);
        $table->addColumn('product_id', 'integer');
        $table->addColumn('channel_id', 'integer');
        $table->addColumn('origin_id', 'integer', [
            'notnull' => false,
            'precision' => 0,
            'unsigned' => true
        ]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(
            ['product_id', 'channel_id'],
            'unq_product_channel_idx'
        );

        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'status',
            'marello_m2_p_status',
            false,
            true,
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM]
            ]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function createMagentoProductForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_m2_product');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
    }

    /**
     * @param Schema $schema
     */
    protected function createMagentoProductTaxTable(Schema $schema)
    {
        $table = $schema->createTable('marello_m2_product_tax_class');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('origin_id', 'integer', [
            'notnull' => false,
            'precision' => 0,
            'unsigned' => true
        ]);
        $table->addColumn('class_name', 'string', ['length' => 255]);
        $table->addColumn('channel_id', 'integer');
        $table->setPrimaryKey(['id']);
    }

    /**
     * @param Schema $schema
     */
    protected function createMagentoProductTaxForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_m2_product_tax_class');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function createProductClassToTaxCodeRelation(Schema $schema)
    {
        $table = $schema->getTable('marello_tax_tax_code');
        $targetTable = $schema->getTable('marello_m2_product_tax_class');

        $this->extendExtension->addManyToOneRelation(
            $schema,
            $targetTable,
            'taxCode',
            $table,
            'code',
            [
                ExtendOptionsManager::MODE_OPTION => ConfigModel::MODE_READONLY,
                'extend' => [
                    'is_extend' => true,
                    'owner' => ExtendScope::OWNER_CUSTOM,
                    'without_default' => true,
                    'on_delete' => 'SET NULL',
                ],
                'dataaudit' => ['auditable' => true]
            ]
        );

        $this->extendExtension->addManyToOneInverseRelation(
            $schema,
            $targetTable,
            'taxCode',
            $table,
            'magento2ProductTaxClasses',
            ['class_name'],
            ['class_name'],
            ['class_name'],
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
        $table->addIndex(['channel_id', 'hash_id'], 'idx_customer_hash_channel_idx');
        $table->addUniqueIndex(['channel_id', 'origin_id'], 'unq_customer_channel_idx');
    }

    /**
     * @param Schema $schema
     */
    protected function createMagentoAttributeSetTable(Schema $schema)
    {
        $table = $schema->createTable('marello_m2_attributeset');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('channel_id', 'integer');
        $table->addColumn('attribute_set_name', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'precision' => 0, 'unsigned' => true]);
        $table->addIndex(['channel_id'], 'IDX_D427981972F5A1AA', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['channel_id', 'origin_id'], 'unq_attributeset_idx');
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
    protected function addMagentoAttributeSetForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_m2_attributeset');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
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
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->addColumn('imported_at', 'datetime');
        $table->addColumn('synced_at', 'datetime');
        $table->addColumn('m2_store_id', 'integer', ['notnull' => false]);
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

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_m2_store'),
            ['m2_store_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function addAttributeSetToAttributeFamilyRelation(Schema $schema)
    {
        $table = $schema->getTable('oro_attribute_family');
        $targetTable = $schema->getTable('marello_m2_attributeset');

        $this->extendExtension->addManyToOneRelation(
            $schema,
            $targetTable,
            'attributeFamily',
            $table,
            'code',
            [
                ExtendOptionsManager::MODE_OPTION => ConfigModel::MODE_READONLY,
                'extend' => [
                    'is_extend' => true,
                    'owner' => ExtendScope::OWNER_CUSTOM,
                    'without_default' => true,
                    'on_delete' => 'SET NULL',
                ],
                'dataaudit' => ['auditable' => true]
            ]
        );

        $this->extendExtension->addManyToOneInverseRelation(
            $schema,
            $targetTable,
            'attributeFamily',
            $table,
            'magento2AttributeSet',
            ['attribute_set_name'],
            ['attribute_set_name'],
            ['attribute_set_name'],
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
