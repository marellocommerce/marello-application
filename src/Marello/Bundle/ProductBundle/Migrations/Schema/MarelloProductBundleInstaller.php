<?php

namespace Marello\Bundle\ProductBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloProductBundleInstaller implements Installation
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
        $this->createMarelloProductProductTable($schema);
        $this->createMarelloProductProductStatusTable($schema);
        $this->createMarelloProductToVariantTable($schema);
        $this->createMarelloProductVariantTable($schema);
        $this->createProductSaleschannelTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloProductProductForeignKeys($schema);
        $this->addMarelloProductToVariantForeignKeys($schema);
        $this->addProductSaleschannelForeignKeys($schema);
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
        $table->addColumn('variant_id', 'integer', ['notnull' => false]);
        $table->addColumn('product_status', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('sku', 'string', ['length' => 255]);
        $table->addColumn('price', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('stock_level', 'float', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('type', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('cost', 'money', ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['sku'], 'marello_product_product_skuidx');
        $table->addIndex(['organization_id'], 'IDX_25845B8D32C8A3DE', []);
        $table->addIndex(['created_at'], 'idx_marello_product_created_at', []);
        $table->addIndex(['updated_at'], 'idx_marello_product_updated_at', []);
        $table->addIndex(['product_status'], 'IDX_25845B8D197C24B8', []);
        $table->addIndex(['variant_id'], 'IDX_25845B8D3B69A9AF', []);
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
        $table->addUniqueIndex(['label'], 'UNIQ_DE31B8C7EA750E8');
    }

    /**
     * Create marello_product_to_variant table
     *
     * @param Schema $schema
     */
    protected function createMarelloProductToVariantTable(Schema $schema)
    {
        $table = $schema->createTable('marello_product_to_variant');
        $table->addColumn('variant_id', 'integer', []);
        $table->addColumn('product_id', 'integer', []);
        $table->setPrimaryKey(['variant_id', 'product_id']);
        $table->addIndex(['variant_id'], 'IDX_6696A623B69A9AF', []);
        $table->addIndex(['product_id'], 'IDX_6696A624584665A', []);
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
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['variant_code'], 'UNIQ_78DE08D98EDA60D');
    }

    /**
     * Create product_saleschannel table
     *
     * @param Schema $schema
     */
    protected function createProductSaleschannelTable(Schema $schema)
    {
        $table = $schema->createTable('product_saleschannel');
        $table->addColumn('product_id', 'integer', []);
        $table->addColumn('saleschannel_id', 'integer', []);
        $table->setPrimaryKey(['product_id', 'saleschannel_id']);
        $table->addIndex(['product_id'], 'IDX_F49A19A74584665A', []);
        $table->addIndex(['saleschannel_id'], 'IDX_F49A19A732758FE', []);
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
            $schema->getTable('marello_product_variant'),
            ['variant_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
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
    }

    /**
     * Add marello_product_to_variant foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloProductToVariantForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_product_to_variant');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_variant'),
            ['variant_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add product_saleschannel foreign keys.
     *
     * @param Schema $schema
     */
    protected function addProductSaleschannelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('product_saleschannel');
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
}
