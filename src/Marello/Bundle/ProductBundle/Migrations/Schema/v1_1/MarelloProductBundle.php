<?php

namespace Marello\Bundle\ProductBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class MarelloProductBundle implements Migration
{
    const PRODUCT_TABLE_NAME = 'marello_product_product';
    const PRODUCT_CHANNEL_TAX_RELATION_TABLE_NAME = 'marello_prod_prod_chan_tax_rel';

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->addMarelloProductProductTable($schema);
        $this->createMarelloProductSalesChannelTaxRelationTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloProductProductForeignKeys($schema);
        $this->addMarelloProductSalesChannelTaxRelationForeignKeys($schema);
    }

    /**
     * Create marello_product_product table
     *
     * @param Schema $schema
     */
    protected function addMarelloProductProductTable(Schema $schema)
    {
        $table = $schema->getTable(self::PRODUCT_TABLE_NAME);
        $table->addColumn('tax_code_id', 'integer', ['notnull' => false]);
        $table->addIndex(['tax_code_id']);
    }

    /**
     * Create marello_prod_prod_chan_tax_rel table
     *
     * @param Schema $schema
     */
    protected function createMarelloProductSalesChannelTaxRelationTable(Schema $schema)
    {
        $table = $schema->createTable(self::PRODUCT_CHANNEL_TAX_RELATION_TABLE_NAME);
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
        $table = $schema->getTable(self::PRODUCT_TABLE_NAME);
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_tax_tax_code'),
            ['tax_code_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_prod_prod_chan_tax_rel foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloProductSalesChannelTaxRelationForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(self::PRODUCT_CHANNEL_TAX_RELATION_TABLE_NAME);
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
}
