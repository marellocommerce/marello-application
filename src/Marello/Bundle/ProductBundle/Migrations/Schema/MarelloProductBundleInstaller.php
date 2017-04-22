<?php

namespace Marello\Bundle\ProductBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloProductBundleInstaller implements Installation, ExtendExtensionAwareInterface
{
    /** @var ExtendExtension */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_2';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloProductProductTable($schema);
        $this->createMarelloProductProductStatusTable($schema);
        $this->createMarelloProductSaleschannelTable($schema);
        $this->createMarelloProductVariantTable($schema);
        $this->createMarelloProductSalesChannelTaxRelationTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloProductProductForeignKeys($schema);
        $this->addMarelloProductSaleschannelForeignKeys($schema);
        $this->addMarelloProductSalesChannelTaxRelationForeignKeys($schema);
    }

    /**
     * Create marello_product_product table
     *
     * @param Schema $schema
     */
    protected function createMarelloProductProductTable(Schema $schema)
    {
        $table = $schema->createTable('marello_product_product');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('product_status', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('variant_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('sku', 'string', ['length' => 255]);
        $table->addColumn('desiredstocklevel', 'integer', []);
        $table->addColumn('purchasestocklevel', 'integer', []);
        $table->addColumn(
            'price',
            'money',
            ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('type', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn(
            'cost',
            'money',
            ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn('data', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
        $table->addColumn('weight', 'float', ['notnull' => false]);
        $table->addColumn('warranty', 'integer', ['notnull' => false]);
        $table->addColumn('preferred_supplier_id', 'integer', ['notnull' => false]);
        $table->addColumn('tax_code_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['sku'], 'marello_product_product_skuidx');
        $table->addIndex(['organization_id'], 'idx_25845b8d32c8a3de', []);
        $table->addIndex(['variant_id'], 'idx_25845b8d3b69a9af', []);
        $table->addIndex(['created_at'], 'idx_marello_product_created_at', []);
        $table->addIndex(['updated_at'], 'idx_marello_product_updated_at', []);
        $table->addIndex(['product_status'], 'IDX_25845B8D197C24B8', []);
        $table->addIndex(['preferred_supplier_id']);
        $table->addIndex(['tax_code_id']);

        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'replenishment',
            'marello_product_reple',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
            ]
        );
    }

    /**
     * Create marello_product_product_status table
     *
     * @param Schema $schema
     */
    protected function createMarelloProductProductStatusTable(Schema $schema)
    {
        $table = $schema->createTable('marello_product_product_status');
        $table->addColumn('name', 'string', ['length' => 32]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->setPrimaryKey(['name']);
        $table->addUniqueIndex(['label'], 'uniq_de31b8c7ea750e8');
    }

    /**
     * Create marello_product_saleschannel table
     *
     * @param Schema $schema
     */
    protected function createMarelloProductSaleschannelTable(Schema $schema)
    {
        $table = $schema->createTable('marello_product_saleschannel');
        $table->addColumn('product_id', 'integer', []);
        $table->addColumn('saleschannel_id', 'integer', []);
        $table->setPrimaryKey(['product_id', 'saleschannel_id']);
        $table->addIndex(['product_id'], 'idx_f49a19a74584665a', []);
        $table->addIndex(['saleschannel_id'], 'idx_f49a19a732758fe', []);
    }

    /**
     * Create marello_product_variant table
     *
     * @param Schema $schema
     */
    protected function createMarelloProductVariantTable(Schema $schema)
    {
        $table = $schema->createTable('marello_product_variant');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('variant_code', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['variant_code'], 'uniq_78de08d98eda60d');
    }

    /**
     * Create marello_prod_prod_chan_tax_rel table
     *
     * @param Schema $schema
     */
    protected function createMarelloProductSalesChannelTaxRelationTable(Schema $schema)
    {
        $table = $schema->createTable('marello_prod_prod_chan_tax_rel');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('product_id', 'integer', ['notnull' => true]);
        $table->addColumn('sales_channel_id', 'integer', ['notnull' => true]);
        $table->addColumn('tax_code_id', 'integer', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(
            ['product_id', 'sales_channel_id', 'tax_code_id'],
            'marello_prod_prod_chan_tax_rel_uidx'
        );
        $table->addIndex(['product_id'], '', []);
        $table->addIndex(['sales_channel_id'], '', []);
        $table->addIndex(['tax_code_id'], '', []);
    }

    /**
     * Add marello_product_product foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloProductProductForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_product_product');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product_status'),
            ['product_status'],
            ['name'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_variant'),
            ['variant_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_tax_tax_code'),
            ['tax_code_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_product_saleschannel foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloProductSaleschannelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_product_saleschannel');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sales_sales_channel'),
            ['saleschannel_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }


    /**
     * Add marello_prod_prod_chan_tax_rel foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloProductSalesChannelTaxRelationForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_prod_prod_chan_tax_rel');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sales_sales_channel'),
            ['sales_channel_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_tax_tax_code'),
            ['tax_code_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Sets the ExtendExtension
     *
     * @param ExtendExtension $extendExtension
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }
}
