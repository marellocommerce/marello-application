<?php

namespace Marello\Bundle\Magento2Bundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloMagento2Bundle implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateOrderTable($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function updateOrderTable(Schema $schema)
    {
        $table = $schema->getTable('marello_m2_order');
        $table->addColumn('imported_at', 'datetime');
        $table->addColumn('synced_at', 'datetime');
    }
}
