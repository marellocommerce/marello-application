<?php

namespace Marello\Bundle\PricingBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloPricingBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_1';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloPriceTypeTable($schema);
        $this->createMarelloProductChannelPriceTable($schema);
        $this->createMarelloProductPriceTable($schema);
        $this->createMarelloAssembledPriceListTable($schema);
        $this->createMarelloAssembledChannelPriceListTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloProductChannelPriceForeignKeys($schema);
        $this->addMarelloProductPriceForeignKeys($schema);
        $this->addMarelloAssembledChannelPriceListForeignKeys($schema);
        $this->addMarelloAssembledPriceListForeignKeys($schema);
    }

    /**
     * Create marello_pricing_price_type table
     *
     * @param Schema $schema
     */
    protected function createMarelloPriceTypeTable(Schema $schema)
    {
        $table = $schema->createTable('marello_pricing_price_type');
        $table->addColumn('name', 'string');
        $table->addColumn('label', 'string');
        $table->setPrimaryKey(['name']);
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
        $table->addColumn('type', 'string', ['notnull' => true]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['product_id', 'channel_id', 'currency', 'type'], 'marello_product_channel_price_uidx');
        $table->addIndex(['channel_id']);
        $table->addIndex(['product_id']);
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
        $table->addColumn('type', 'string', ['notnull' => true]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['product_id', 'currency', 'type'], 'marello_product_price_uidx');
        $table->addIndex(['product_id']);
    }

    /**
     * Create marello_pricing_price_type table
     *
     * @param Schema $schema
     */
    protected function createMarelloAssembledPriceListTable(Schema $schema)
    {
        $table = $schema->createTable('marello_assembled_price_list');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('product_id', 'integer', []);
        $table->addColumn('default_price_id', 'integer', []);
        $table->addColumn('special_price_id', 'integer', ['notnull' => false]);
        $table->addColumn('msrp_price_id', 'integer', ['notnull' => false]);
        $table->addColumn('currency', 'string', ['length' => 3]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['product_id']);
        $table->addUniqueIndex(['default_price_id'], 'marello_assembled_pr_dfp_uidx');
        $table->addUniqueIndex(['special_price_id'], 'marello_assembled_pr_sfp_uidx');
        $table->addUniqueIndex(['msrp_price_id'], 'marello_assembled_pr_mfp_uidx');
    }

    /**
     * Create marello_pricing_price_type table
     *
     * @param Schema $schema
     */
    protected function createMarelloAssembledChannelPriceListTable(Schema $schema)
    {
        $table = $schema->createTable('marello_assembled_ch_pr_list');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('product_id', 'integer', []);
        $table->addColumn('channel_id', 'integer', []);
        $table->addColumn('default_price_id', 'integer', []);
        $table->addColumn('special_price_id', 'integer', ['notnull' => false]);
        $table->addColumn('currency', 'string', ['length' => 3]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['default_price_id'], 'marello_assembled_ch_pr_dfp_uidx');
        $table->addUniqueIndex(['special_price_id'], 'marello_assembled_ch_pr_sfp_uidx');
        $table->addIndex(['channel_id']);
        $table->addIndex(['product_id']);
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
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_pricing_price_type'),
            ['type'],
            ['name'],
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
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_pricing_price_type'),
            ['type'],
            ['name'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_product_channel_price foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloAssembledChannelPriceListForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_assembled_ch_pr_list');
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
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_channel_price'),
            ['default_price_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_channel_price'),
            ['special_price_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_product_price foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloAssembledPriceListForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_assembled_price_list');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_price'),
            ['default_price_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_price'),
            ['special_price_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_price'),
            ['msrp_price_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
