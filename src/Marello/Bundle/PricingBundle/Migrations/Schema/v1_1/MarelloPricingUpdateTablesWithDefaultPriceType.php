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
class MarelloPricingUpdateTablesWithDefaultPriceType implements Migration, OrderedMigrationInterface
{
    public function getOrder()
    {
        return 2;
    }
    
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables modification **/
        $this->updateMarelloProductChannelPriceTable($schema, $queries);
        $this->updateMarelloProductPriceTable($schema, $queries);
    }

    /**
     * Update marello_product_channel_price table
     *
     * @param Schema $schema
     * @param QueryBag $queries
     */
    protected function updateMarelloProductChannelPriceTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_product_channel_price');
        $table->addColumn('type', 'string', ['notnull' => false]);

        $query = "
            UPDATE marello_product_channel_price cp
                SET
                    cp.type = 'default'
        ";
        $queries->addQuery($query);
    }

    /**
     * Update marello_product_price table
     *
     * @param Schema $schema
     * @param QueryBag $queries
     */
    protected function updateMarelloProductPriceTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_product_price');
        $table->addColumn('type', 'string', ['notnull' => false]);


        $query = "
            UPDATE marello_product_price pp
                SET
                    pp.type = 'default'
        ";
        $queries->addQuery($query);
    }
}
