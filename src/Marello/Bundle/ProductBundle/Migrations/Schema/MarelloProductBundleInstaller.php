<?php

namespace Marello\Bundle\ProductBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareTrait;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloProductBundleInstaller implements
    Installation,
    AttachmentExtensionAwareInterface,
    ExtendExtensionAwareInterface
{
    use AttachmentExtensionAwareTrait;

    /** @var ExtendExtension $extendExtension */
    protected $extendExtension;

    const PRODUCT_TABLE = 'marello_product_product';
    const MAX_PRODUCT_IMAGE_SIZE_IN_MB = 1;
    const MAX_PRODUCT_IMAGE_DIMENSIONS_IN_PIXELS = 250;

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_14';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloProductProductTable($schema);
        $this->createMarelloProductProductNameTable($schema);
        $this->createMarelloProductProductStatusTable($schema);
        $this->createMarelloProductSaleschannelTable($schema);
        $this->createMarelloProductVariantTable($schema);
        $this->createMarelloProductSalesChannelTaxRelationTable($schema);
        $this->createMarelloProductSupplierRelationTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloProductProductForeignKeys($schema);
        $this->addMarelloProductNameForeignKeys($schema);
        $this->addMarelloProductSaleschannelForeignKeys($schema);
        $this->addMarelloProductSalesChannelTaxRelationForeignKeys($schema);
        $this->addMarelloProductSupplierRelationForeignKeys($schema);

        /** Add Image attribute relation **/
        $this->addImageRelation($schema);

        /** Add attribute family and attribute family relation **/
        $this->addAttributeFamily($schema);
    }

    /**
     * Create marello_product_product table
     *
     * @param Schema $schema
     */
    protected function createMarelloProductProductTable(Schema $schema)
    {
        $table = $schema->createTable(self::PRODUCT_TABLE);
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('product_status', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('variant_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('sku', 'string', ['length' => 255]);
        $table->addColumn('manufacturing_code', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('barcode', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('type', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('data', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
        $table->addColumn('weight', 'float', ['notnull' => false]);
        $table->addColumn('warranty', 'integer', ['notnull' => false]);
        $table->addColumn('preferred_supplier_id', 'integer', ['notnull' => false]);
        $table->addColumn('tax_code_id', 'integer', ['notnull' => false]);
        $table->addColumn('channels_codes', 'text', ['notnull' => false, 'comment' => '(DC2Type:text)']);
        $table->addColumn('categories_codes', 'text', ['notnull' => false, 'comment' => '(DC2Type:text)']);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['sku', 'organization_id'], 'marello_product_product_skuorgidx');
        $table->addIndex(['created_at'], 'idx_marello_product_created_at', []);
        $table->addIndex(['updated_at'], 'idx_marello_product_updated_at', []);
        $table->addIndex(['product_status'], 'IDX_25845B8D197C24B8', []);
        $table->addIndex(['preferred_supplier_id']);
        $table->addIndex(['tax_code_id']);
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloProductProductNameTable(Schema $schema)
    {
        $table = $schema->createTable('marello_product_product_name');
        $table->addColumn('product_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['product_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id'], 'uniq_58b39126eb576e89');
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
    }

    /**
     * Create marello_product_prod_supp_rel table
     *
     * @param Schema $schema
     */
    protected function createMarelloProductSupplierRelationTable(Schema $schema)
    {
        $table = $schema->createTable('marello_product_prod_supp_rel');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('product_id', 'integer', ['notnull' => true]);
        $table->addColumn('supplier_id', 'integer', ['notnull' => true]);
        $table->addColumn('quantity_of_unit', 'integer', ['notnull' => true]);
        $table->addColumn('priority', 'integer', []);
        $table->addColumn(
            'cost',
            'money',
            ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn('can_dropship', 'boolean', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(
            [
                'product_id',
                'supplier_id',
                'quantity_of_unit'
            ],
            'marello_product_prod_supp_rel_uidx'
        );
    }

    /**
     * Add marello_product_product foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloProductProductForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(self::PRODUCT_TABLE);
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
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_supplier_supplier'),
            ['preferred_supplier_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloProductNameForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_product_product_name');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
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
            $schema->getTable(self::PRODUCT_TABLE),
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
            $schema->getTable(self::PRODUCT_TABLE),
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
     * Add marello_product_prod_supp_rel foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloProductSupplierRelationForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_product_prod_supp_rel');
        $table->addForeignKeyConstraint(
            $schema->getTable(self::PRODUCT_TABLE),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_supplier_supplier'),
            ['supplier_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function addImageRelation(Schema $schema)
    {
        $this->attachmentExtension->addImageRelation(
            $schema,
            self::PRODUCT_TABLE,
            'image',
            [
                'importexport' => ['excluded' => true],
                'attribute' => ['is_attribute' => true],
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'attachment' => [
                    'acl_protected' => false
                ]
            ],
            self::MAX_PRODUCT_IMAGE_SIZE_IN_MB,
            self::MAX_PRODUCT_IMAGE_DIMENSIONS_IN_PIXELS,
            self::MAX_PRODUCT_IMAGE_DIMENSIONS_IN_PIXELS
        );

        $attachmentTable = $schema->getTable('oro_attachment_file');
        if (!$attachmentTable->hasColumn('media_url')) {
            $attachmentTable->addColumn('media_url', 'string', [
                'oro_options' => [
                    'extend' => [
                        'is_extend' => true,
                        'owner' => ExtendScope::OWNER_CUSTOM,
                        'nullable' => true,
                        'on_delete' => 'SET NULL'
                    ],
                    'entity' => [
                        'label' => 'marello.attachment.file.media_url.label',
                        'description' => 'marello.attachment.file.media_url.description'
                    ]
                ],
                [
                    'length' => 255,
                    'notnull' => false
                ]
            ]);
        }
    }

    /**
     * @param Schema $schema
     */
    protected function addAttributeFamily(Schema $schema)
    {
        $table = $schema->getTable(self::PRODUCT_TABLE);
        $table->addColumn('attribute_family_id', 'integer', ['notnull' => false]);
        $table->addIndex(['attribute_family_id']);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_attribute_family'),
            ['attribute_family_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'RESTRICT']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }
}
