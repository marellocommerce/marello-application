<?php

namespace Marello\Bundle\SalesBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloSalesBundle implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_sales_channel_group');
        $table->addColumn('integration_channel_id', 'integer', ['notnull' => false]);
        $table->addUniqueIndex(['integration_channel_id'], 'UNIQ_759DCFAB3D6A9E29');

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['integration_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
