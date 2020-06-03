<?php

namespace Marello\Bundle\SalesBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloSalesBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->modifyMarelloSalesSalesChannelTable($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function modifyMarelloSalesSalesChannelTable(Schema $schema)
    {
        $table = $schema->getTable('marello_sales_sales_channel');
        if ($table->hasIndex('UNIQ_75C456C9F5B7AF7511')) {
            $table->dropIndex('UNIQ_75C456C9F5B7AF7511');
        }
    }

}
