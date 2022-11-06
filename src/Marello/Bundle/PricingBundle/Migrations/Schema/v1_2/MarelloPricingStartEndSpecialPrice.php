<?php

namespace Marello\Bundle\PricingBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloPricingStartEndSpecialPrice implements Migration, OrderedMigrationInterface
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
        $this->addStartEndFieldsForProductPriceTable($schema);
        $this->addStartEndFieldsForProductChannelPriceTable($schema);
    }

    protected function addStartEndFieldsForProductPriceTable(Schema $schema)
    {
        $table = $schema->getTable('marello_product_price');
        $table->addColumn('start_date', 'datetime', ['notnull' => false]);
        $table->addColumn('end_date', 'datetime', ['notnull' => false]);
    }

    protected function addStartEndFieldsForProductChannelPriceTable(Schema $schema)
    {
        $table = $schema->getTable('marello_product_channel_price');
        $table->addColumn('start_date', 'datetime', ['notnull' => false]);
        $table->addColumn('end_date', 'datetime', ['notnull' => false]);
    }
}
