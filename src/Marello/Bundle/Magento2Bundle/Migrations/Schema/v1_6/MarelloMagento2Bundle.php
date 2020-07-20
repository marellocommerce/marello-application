<?php

namespace Marello\Bundle\Magento2Bundle\Migrations\Schema\v1_6;

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
        $this->dropUnneededColumns($schema);
        $this->addNewSyncStartDateColumns($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function dropUnneededColumns(Schema $schema)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->dropColumn('m2_initial_sync_start_date');
        $table->dropColumn('m2_sync_start_date');
        $table->dropColumn('m2_sync_range');
    }

    /**
     * @param Schema $schema
     */
    protected function addNewSyncStartDateColumns(Schema $schema)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('m2_initial_sync_start_date', 'date', ['notnull' => false]);
        $table->addColumn('m2_sync_start_date', 'datetime', ['notnull' => false]);
    }
}
