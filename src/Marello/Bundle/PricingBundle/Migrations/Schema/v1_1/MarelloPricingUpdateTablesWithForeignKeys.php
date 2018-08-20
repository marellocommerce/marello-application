<?php

namespace Marello\Bundle\PricingBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloPricingUpdateTablesWithForeignKeys implements Migration, OrderedMigrationInterface
{
    public function getOrder()
    {
        return 3;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables modification **/
        $this->updateMarelloProductChannelPriceTable($schema);
        $this->updateMarelloProductPriceTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloProductChannelPriceForeignKeys($schema);
        $this->addMarelloProductPriceForeignKeys($schema);
    }

    /**
     * Update marello_product_channel_price table
     *
     * @param Schema $schema
     */
    protected function updateMarelloProductChannelPriceTable(Schema $schema)
    {
        $table = $schema->getTable('marello_product_channel_price');
        $table->changeColumn('type', ['notnull' => true]);
        $table->dropIndex('marello_product_channel_price_uidx');
        $table->addUniqueIndex(['product_id', 'channel_id', 'currency', 'type'], 'marello_product_channel_price_uidx');
    }

    /**
     * Update marello_product_price table
     *
     * @param Schema $schema
     */
    protected function updateMarelloProductPriceTable(Schema $schema)
    {
        $table = $schema->getTable('marello_product_price');
        $table->changeColumn('type', ['notnull' => true]);
        $table->dropIndex('marello_product_price_uidx');
        $table->addUniqueIndex(['product_id', 'currency', 'type'], 'marello_product_price_uidx');
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
            $schema->getTable('marello_pricing_price_type'),
            ['type'],
            ['name'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
