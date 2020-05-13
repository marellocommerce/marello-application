<?php

namespace Marello\Bundle\SalesBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloSalesBundleDropSystemColumn implements Migration, OrderedMigrationInterface
{
    /**
     * @inheritDoc
     */
    public function getOrder()
    {
        return 20;
    }


    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateMarelloSalesChannelGroupTable($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function updateMarelloSalesChannelGroupTable(Schema $schema)
    {
        $table = $schema->getTable('marello_sales_channel_group');

        $table->dropColumn('system');

    }
}
