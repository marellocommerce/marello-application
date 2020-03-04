<?php

namespace Marello\Bundle\PricingBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloPricingBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloProductChannelPriceTable($schema);
        $this->createMarelloProductPriceTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloProductChannelPriceForeignKeys($schema);
        $this->addMarelloProductPriceForeignKeys($schema);
    }

    /**
     * Create marello_product_channel_price table
     *
     * @param Schema $schema
     */
    protected function createMarelloProductChannelPriceTable(Schema $schema)
    {
        $table = $schema->createTable('marello_product_channel_price');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('product_id', 'integer', []);
        $table->addColumn('channel_id', 'integer', []);
        $table->addColumn('value', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('currency', 'string', ['length' => 3]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['product_id', 'channel_id', 'currency'], 'marello_product_channel_price_uidx');
        $table->addIndex(['channel_id'], 'IDX_E7DDE13A72F5A1AA', []);
        $table->addIndex(['product_id'], 'IDX_E7DDE13A4584665A', []);
    }

    /**
     * Create marello_product_price table
     *
     * @param Schema $schema
     */
    protected function createMarelloProductPriceTable(Schema $schema)
    {
        $table = $schema->createTable('marello_product_price');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('product_id', 'integer', []);
        $table->addColumn('value', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('currency', 'string', ['length' => 3]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['product_id', 'currency'], 'marello_product_price_uidx');
        $table->addIndex(['product_id'], 'IDX_B48361D84584665A', []);
    }

    /**
     * Add marello_product_channel_price foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloProductChannelPriceForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_product_channel_price');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sales_sales_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_product_price foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloProductPriceForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_product_price');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
