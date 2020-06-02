<?php

namespace Marello\Bundle\OroCommerceBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloOroCommerceBundle implements Migration
{
    /**
     * {@inheritdoc}
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateOroIntegrationTransportTable($schema, $queries);
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    public function updateOroIntegrationTransportTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('orocommerce_scg_id', 'integer', ['notnull' => false]);
        $table->addIndex(['orocommerce_scg_id'], null, []);

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sales_channel_group'),
            ['orocommerce_scg_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }
}
