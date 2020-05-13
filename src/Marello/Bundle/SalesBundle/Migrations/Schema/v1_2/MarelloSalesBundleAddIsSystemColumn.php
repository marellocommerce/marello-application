<?php

namespace Marello\Bundle\SalesBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloSalesBundleAddIsSystemColumn implements Migration, OrderedMigrationInterface
{
    /**
     * @inheritDoc
     */
    public function getOrder()
    {
        return 10;
    }


    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateMarelloSalesChannelGroupTable($schema, $queries);
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    protected function updateMarelloSalesChannelGroupTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_sales_channel_group');

        $table->addColumn('is_system', 'boolean', ['default' => false]);
        $query = "
            UPDATE marello_sales_channel_group
                SET
                    is_system = system";
        $queries->addQuery($query);
    }
}
