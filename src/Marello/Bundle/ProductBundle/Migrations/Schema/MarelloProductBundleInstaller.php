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
        $this->createMarelloProductProductStatusTable($schema);
        $this->createProductSaleschannelTable($schema);
        $this->createMarelloProductVariantTable($schema);
        $this->createMarelloProductToVariantTable($schema);
        $this->createMarelloProductProductTable($schema);

        /** Foreign keys generation **/
        $this->addProductSaleschannelForeignKeys($schema);
        $this->addMarelloProductToVariantForeignKeys($schema);
        $this->addMarelloProductProductForeignKeys($schema);
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
        $table->addUniqueIndex(['label'], 'uniq_de31b8c7ea750e8');
        $table->setPrimaryKey(['name']);
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
        $table->addIndex(['product_id'], 'idx_f49a19a74584665a', []);
        $table->setPrimaryKey(['product_id', 'saleschannel_id']);
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
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['variant_code'], 'uniq_78de08d98eda60d');
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
        $table->addIndex(['product_id'], 'idx_6696a624584665a', []);
        $table->addIndex(['variant_id'], 'idx_6696a623b69a9af', []);
        $table->setPrimaryKey(['variant_id', 'product_id']);
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
        $table->addColumn('marello_product_status_name', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('variant_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('sku', 'string', ['length' => 255]);
        $table->addColumn('price', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('type', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('cost', 'money', ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addIndex(['updated_at'], 'idx_marello_product_updated_at', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['sku'], 'marello_product_product_skuidx');
        $table->addIndex(['organization_id'], 'idx_25845b8d32c8a3de', []);
        $table->addIndex(['variant_id'], 'idx_25845b8d3b69a9af', []);
        $table->addIndex(['marello_product_status_name'], 'idx_25845b8da050b8c8', []);
        $table->addIndex(['created_at'], 'idx_marello_product_created_at', []);
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
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sales_sales_channel'),
            ['saleschannel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
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
     * Add marello_product_product foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloProductProductForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_product_product');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product_status'),
            ['marello_product_status_name'],
            ['name'],
            ['onUpdate' => null, 'onDelete' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_variant'),
            ['variant_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }
}
