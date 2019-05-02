<?php

namespace Marello\Bundle\PricingBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use Marello\Bundle\PricingBundle\Model\PriceTypeInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloPricingPriceTypeTable implements Migration, OrderedMigrationInterface
{
    /**
     * @inheritDoc
     */
    public function getOrder()
    {
        return 1;
    }
    
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloPriceTypeTable($schema, $queries);
    }

    /**
     * Create marello_pricing_price_type table
     *
     * @param Schema $schema
     * @param QueryBag $queries
     */
    protected function createMarelloPriceTypeTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->createTable('marello_pricing_price_type');
        $table->addColumn('name', 'string');
        $table->addColumn('label', 'string');
        $table->setPrimaryKey(['name']);

        $defaultPriceLabel = sprintf('%s Price', ucfirst(PriceTypeInterface::DEFAULT_PRICE));
        $specialPriceLabel = sprintf('%s Price', ucfirst(PriceTypeInterface::SPECIAL_PRICE));
        $query = "
            INSERT INTO marello_pricing_price_type (`name`, `label`)
            VALUES 
            ('" . PriceTypeInterface::DEFAULT_PRICE . "', '" . $defaultPriceLabel . "'),
            ('" . PriceTypeInterface::SPECIAL_PRICE . "', '" . $specialPriceLabel . "')
        ";
        $queries->addQuery($query);
    }
}
